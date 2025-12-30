# -*- coding: utf-8 -*-
"""
AllMight - Worker de Coleta de Licitações PNCP
Worker de produção que roda 24/7 coletando licitações automaticamente
"""

import sys
import io
import logging
from logging.handlers import RotatingFileHandler
import schedule
import time as time_module
from datetime import datetime, time
import os
import mysql.connector
from mysql.connector import Error
import requests
import json
from concurrent.futures import ThreadPoolExecutor, as_completed
import threading
import uuid
import re

# ============================================================================
# CONFIGURAÇÃO DE LOGGING
# ============================================================================
def setup_logger():
    """Configura sistema de logs com rotação de arquivos"""
    logger = logging.getLogger('worker_licitacao')
    logger.setLevel(logging.INFO)
    
    # Criar pasta de logs se não existir
    if not os.path.exists('logs'):
        os.makedirs('logs')
    
    # Handler para arquivo com rotação (máximo 10MB, mantém 5 backups)
    file_handler = RotatingFileHandler(
        'logs/worker_licitacao.log',
        maxBytes=10*1024*1024,  # 10MB
        backupCount=5,
        encoding='utf-8'
    )
    file_handler.setLevel(logging.INFO)
    
    # Handler para console (quando rodar manualmente)
    console_handler = logging.StreamHandler()
    console_handler.setLevel(logging.INFO)
    
    # Formato detalhado dos logs
    formatter = logging.Formatter(
        '%(asctime)s - %(name)s - %(levelname)s - %(message)s',
        datefmt='%Y-%m-%d %H:%M:%S'
    )
    file_handler.setFormatter(formatter)
    console_handler.setFormatter(formatter)
    
    logger.addHandler(file_handler)
    logger.addHandler(console_handler)
    
    return logger

# Inicializar logger global
logger = setup_logger()

# ============================================================================
# CONFIGURAÇÕES DO MYSQL
# ============================================================================
MYSQL_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'allmight',
    'charset': 'utf8mb4',
    'collation': 'utf8mb4_unicode_ci',
    'autocommit': False,
    'connect_timeout': 10,
    'pool_reset_session': True
}

# ============================================================================
# CONFIGURAÇÕES DA COLETA
# ============================================================================
DELAY_ENTRE_REQUESTS = 0.1
MAX_THREADS = 6
MAX_DOWNLOAD_THREADS = 12
MAX_LICITACAO_THREADS = 4
FAZER_DOWNLOAD_ARQUIVOS = True
PASTA_DOWNLOADS = "downloads_licitacoes"
DOWNLOAD_TIMEOUT = 30
HORARIO_COLETA = "12:16"  # Horário diário da coleta

# Todas as UFs do Brasil (sempre coleta completa)
TODAS_UFS = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 
             'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 
             'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']

# Variáveis globais
db_lock = threading.Lock()
job_em_execucao = False  # Flag para impedir coletas concorrentes
job_lock = threading.Lock()
download_executor = None
licitacao_executor = None
FONTE_PNCP_ID = None

# ============================================================================
# FUNÇÕES DE CONEXÃO COM MYSQL
# ============================================================================

def criar_conexao():
    """Cria uma conexão com o MySQL"""
    try:
        conn = mysql.connector.connect(**MYSQL_CONFIG)
        return conn
    except Error as e:
        logger.error(f"Erro ao conectar ao MySQL: {e}")
        return None

def verificar_banco_existe():
    """Verifica se o banco e a fonte PNCP existem"""
    global FONTE_PNCP_ID
    
    logger.info("Verificando conexão com MySQL...")
    
    try:
        conn = criar_conexao()
        if not conn:
            logger.error("Não foi possível conectar ao MySQL!")
            return False
        
        cursor = conn.cursor(dictionary=True)
        
        # Verificar se as tabelas existem
        cursor.execute("SHOW TABLES LIKE 'licitacoes'")
        if not cursor.fetchone():
            logger.error("Tabela 'licitacoes' não encontrada!")
            conn.close()
            return False
        
        # Buscar ID da fonte PNCP
        cursor.execute("SELECT id FROM fontes_licitacao WHERE tipo_portal = 'PNCP' LIMIT 1")
        fonte = cursor.fetchone()
        
        if fonte:
            FONTE_PNCP_ID = fonte['id']
            logger.info(f"Conectado ao MySQL - Fonte PNCP ID: {FONTE_PNCP_ID}")
        else:
            logger.warning("Fonte PNCP não encontrada!")
            conn.close()
            return False
        
        cursor.close()
        conn.close()
        return True
        
    except Error as e:
        logger.error(f"Erro ao verificar banco: {e}")
        return False

def verificar_coleta_hoje_completa():
    """Verifica se já foi feita coleta completa hoje"""
    try:
        conn = criar_conexao()
        if not conn:
            return False
        
        cursor = conn.cursor()
        
        # Verificar se há licitações atualizadas hoje
        cursor.execute("""
            SELECT COUNT(*) as total
            FROM licitacoes 
            WHERE DATE(data_atualizacao) = CURDATE()
        """)
        
        resultado = cursor.fetchone()
        total_hoje = resultado[0] if resultado else 0
        
        cursor.close()
        conn.close()
        
        # Considera completa se tem mais de 100 licitações atualizadas hoje
        if total_hoje > 100:
            logger.info(f"Coleta de hoje já realizada: {total_hoje} licitações atualizadas")
            return True
        
        return False
        
    except Exception as e:
        logger.error(f"Erro ao verificar coleta: {e}")
        return False

# ============================================================================
# FUNÇÕES DE API
# ============================================================================

def buscar_detalhes_licitacao(cnpj, ano, sequencial):
    """Busca os detalhes completos de uma licitação"""
    url = f"https://pncp.gov.br/api/pncp/v1/orgaos/{cnpj}/compras/{ano}/{sequencial}"
    
    try:
        response = requests.get(url, timeout=30)
        if response.status_code == 200:
            return response.json()
        else:
            return None
    except Exception:
        return None

def buscar_itens_licitacao(cnpj, ano, sequencial):
    """Busca todos os itens de uma licitação"""
    url = f"https://pncp.gov.br/api/pncp/v1/orgaos/{cnpj}/compras/{ano}/{sequencial}/itens"
    
    todos_itens = []
    pagina = 1
    
    while True:
        try:
            params = {"pagina": pagina, "tamanhoPagina": 100}
            response = requests.get(url, params=params, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                if isinstance(data, list):
                    if not data:
                        break
                    todos_itens.extend(data)
                    pagina += 1
                else:
                    break
            elif response.status_code in [404, 204]:
                break
            else:
                break
                
            time_module.sleep(DELAY_ENTRE_REQUESTS)
        except Exception:
            break
    
    return todos_itens

def buscar_arquivos_licitacao(cnpj, ano, sequencial):
    """Busca todos os arquivos de uma licitação"""
    url = f"https://pncp.gov.br/api/pncp/v1/orgaos/{cnpj}/compras/{ano}/{sequencial}/arquivos"
    
    todos_arquivos = []
    pagina = 1
    
    while True:
        try:
            params = {"pagina": pagina, "tamanhoPagina": 100}
            response = requests.get(url, params=params, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                if isinstance(data, list):
                    if not data:
                        break
                    todos_arquivos.extend(data)
                    pagina += 1
                else:
                    break
            elif response.status_code in [404, 204]:
                break
            else:
                break
                
            time_module.sleep(DELAY_ENTRE_REQUESTS)
        except Exception:
            break
    
    return todos_arquivos

def buscar_historico_licitacao(cnpj, ano, sequencial):
    """Busca todo o histórico de alterações de uma licitação"""
    url = f"https://pncp.gov.br/api/pncp/v1/orgaos/{cnpj}/compras/{ano}/{sequencial}/historicos"
    
    todo_historico = []
    pagina = 1
    
    while True:
        try:
            params = {"pagina": pagina, "tamanhoPagina": 100}
            response = requests.get(url, params=params, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                if isinstance(data, list):
                    if not data:
                        break
                    todo_historico.extend(data)
                    pagina += 1
                else:
                    break
            elif response.status_code in [404, 204]:
                break
            else:
                break
                
            time_module.sleep(DELAY_ENTRE_REQUESTS)
        except Exception:
            break
    
    return todo_historico

# ============================================================================
# FUNÇÕES DE DOWNLOAD
# ============================================================================

def sanitizar_nome_arquivo(nome):
    """Remove caracteres inválidos de nomes de arquivo"""
    nome_limpo = re.sub(r'[<>:"/\\|?*]', '_', nome)
    if len(nome_limpo) > 200:
        nome_limpo = nome_limpo[:200]
    return nome_limpo

def baixar_arquivo(url, caminho_destino):
    """Baixa um arquivo da URL para o caminho destino"""
    try:
        os.makedirs(os.path.dirname(caminho_destino), exist_ok=True)
        response = requests.get(url, timeout=DOWNLOAD_TIMEOUT, stream=True)
        response.raise_for_status()
        
        with open(caminho_destino, 'wb') as f:
            for chunk in response.iter_content(chunk_size=65536):
                if chunk:
                    f.write(chunk)
        
        return True, os.path.getsize(caminho_destino)
    except Exception as e:
        return False, str(e)

def organizar_arquivos_licitacao(licitacao_id, cnpj, ano, sequencial, titulo, arquivos):
    """Organiza e baixa arquivos de uma licitação em pastas categorizadas"""
    
    if not FAZER_DOWNLOAD_ARQUIVOS or not arquivos:
        return
    
    titulo_limpo = sanitizar_nome_arquivo(titulo)
    pasta_base = os.path.join(PASTA_DOWNLOADS, cnpj, str(ano), f"{sequencial}_{titulo_limpo}")
    
    categorias = {
        "edital": "editais",
        "ata": "atas",
        "termo": "termos",
        "aviso": "avisos",
        "anexo": "anexos",
    }
    
    for arquivo in arquivos:
        url_download = arquivo.get("url")
        tipo_doc = arquivo.get("tipoDocumentoNome", "outros").lower()
        nome_arquivo = arquivo.get("titulo", f"arquivo_{arquivo.get('sequencialDocumento', 'sem_seq')}")
        nome_arquivo = sanitizar_nome_arquivo(nome_arquivo)
        
        categoria = "outros"
        for chave, pasta in categorias.items():
            if chave in tipo_doc:
                categoria = pasta
                break
        
        pasta_categoria = os.path.join(pasta_base, categoria)
        caminho_arquivo = os.path.join(pasta_categoria, nome_arquivo)
        
        if os.path.exists(caminho_arquivo):
            continue
        
        if url_download and download_executor:
            download_executor.submit(baixar_arquivo, url_download, caminho_arquivo)

# ============================================================================
# FUNÇÃO PARA SALVAR LICITAÇÃO NO MYSQL
# ============================================================================

def popular_licitacao_no_banco(licitacao, itens, arquivos, historico):
    """Salva licitação completa no banco MySQL"""
    
    with db_lock:
        conn = criar_conexao()
        if not conn:
            return False
        
        try:
            cursor = conn.cursor()
            
            # Extrair dados (mesma lógica do original)
            if "orgao_cnpj" in licitacao:
                cnpj = licitacao.get("orgao_cnpj")
                ano = licitacao.get("ano")
                sequencial = licitacao.get("numero_sequencial")
                orgao_nome = licitacao.get("orgao_nome")
                unidade_nome = licitacao.get("unidade_nome")
                uf = licitacao.get("uf")
                municipio_nome = licitacao.get("municipio_nome")
                modalidade_nome = licitacao.get("modalidade_licitacao_nome")
                modalidade_id = licitacao.get("modalidade_licitacao_id")
                situacao_nome = licitacao.get("situacao_nome")
                situacao_id = licitacao.get("situacao_id")
                titulo = licitacao.get("title", "")
                descricao = licitacao.get("description")
                numero_controle = licitacao.get("numero_controle_pncp")
                numero_edital = licitacao.get("numero")
                valor_global = licitacao.get("valor_global")
                data_publicacao = licitacao.get("data_publicacao_pncp")
                data_inicio = licitacao.get("data_inicio_vigencia")
                data_fim = licitacao.get("data_fim_vigencia")
            else:
                orgao = licitacao.get("orgaoEntidade", {})
                unidade = licitacao.get("unidadeOrgao", {})
                modalidade = licitacao.get("modalidadeLicitacao", {})
                situacao = licitacao.get("situacaoCompra", {})
                municipio = licitacao.get("municipio", {})
                cnpj = orgao.get("cnpj")
                ano = licitacao.get("anoCompra")
                sequencial = licitacao.get("sequencialCompra")
                orgao_nome = orgao.get("razaoSocial")
                unidade_nome = unidade.get("nomeUnidade")
                uf = orgao.get("uf")
                municipio_nome = municipio.get("nome")
                modalidade_nome = modalidade.get("nome")
                modalidade_id = modalidade.get("id")
                situacao_nome = situacao.get("nome")
                situacao_id = situacao.get("id")
                titulo = licitacao.get("objetoCompra", "")
                descricao = licitacao.get("informacaoComplementar")
                numero_controle = licitacao.get("numeroControlePNCP")
                numero_edital = licitacao.get("numeroCompra")
                valor_global = licitacao.get("valorTotalEstimado")
                data_publicacao = licitacao.get("dataPublicacaoPncp")
                data_inicio = licitacao.get("dataInicioVigencia")
                data_fim = licitacao.get("dataFimVigencia")
            
            id_externo = f"{cnpj}_{ano}_{sequencial}"
            url_navegador = f"https://pncp.gov.br/app/editais/{cnpj}/{ano}/{sequencial}"
            
            # Verificar se já existe
            cursor.execute("SELECT id FROM licitacoes WHERE id_externo = %s", (id_externo,))
            licitacao_existente = cursor.fetchone()
            
            if licitacao_existente:
                licitacao_uuid = licitacao_existente[0]
                cursor.execute("DELETE FROM licitacao_itens WHERE licitacao_id = %s", (licitacao_uuid,))
                cursor.execute("DELETE FROM licitacao_arquivos WHERE licitacao_id = %s", (licitacao_uuid,))
                cursor.execute("DELETE FROM licitacao_historico WHERE licitacao_id = %s", (licitacao_uuid,))
            else:
                licitacao_uuid = str(uuid.uuid4())
            
            # Mapear status
            status_mapeado = "Aberta"
            if situacao_nome:
                situacao_lower = situacao_nome.lower()
                if "recebendo" in situacao_lower or "aceite" in situacao_lower or "proposta" in situacao_lower:
                    status_mapeado = "Aberta"
                elif "finalizada" in situacao_lower or "encerrada" in situacao_lower or "homolog" in situacao_lower:
                    status_mapeado = "Encerrada"
                elif "suspensa" in situacao_lower or "cancelada" in situacao_lower:
                    status_mapeado = "Cancelada"
            
            # INSERT licitação
            sql_licitacao = """
                INSERT INTO licitacoes (
                    id, fonte_id, id_externo, numero_controle_pncp, numero_edital,
                    titulo, descricao, objeto,
                    orgao_cnpj, orgao_nome, unidade_compradora,
                    uf, municipio,
                    modalidade, modalidade_codigo,
                    situacao, situacao_codigo, status,
                    data_publicacao, data_inicio_vigencia, data_fim_vigencia,
                    valor_estimado,
                    link_portal, tem_edital, tem_anexos,
                    dados_completos_json,
                    ativo, data_insercao, data_atualizacao
                ) VALUES (
                    %s, %s, %s, %s, %s,
                    %s, %s, %s,
                    %s, %s, %s,
                    %s, %s,
                    %s, %s,
                    %s, %s, %s,
                    %s, %s, %s,
                    %s,
                    %s, %s, %s,
                    %s,
                    %s, %s, %s
                )
                ON DUPLICATE KEY UPDATE
                    titulo = VALUES(titulo),
                    situacao = VALUES(situacao),
                    valor_estimado = VALUES(valor_estimado),
                    dados_completos_json = VALUES(dados_completos_json),
                    data_atualizacao = VALUES(data_atualizacao)
            """
            
            valores_licitacao = (
                licitacao_uuid, FONTE_PNCP_ID, id_externo, numero_controle, numero_edital,
                titulo[:500] if titulo else "Sem título", descricao, titulo,
                cnpj, orgao_nome, unidade_nome,
                uf, municipio_nome,
                modalidade_nome, modalidade_id,
                status_mapeado, situacao_id, status_mapeado,
                data_publicacao, data_inicio, data_fim,
                valor_global,
                url_navegador, len(arquivos) > 0, len(arquivos) > 0,
                json.dumps(licitacao, ensure_ascii=False),
                True, datetime.now(), datetime.now()
            )
            
            cursor.execute(sql_licitacao, valores_licitacao)
            
            # Inserir itens
            for item in itens:
                sql_item = """
                    INSERT INTO licitacao_itens (
                        licitacao_id, numero_item, descricao,
                        tipo, material_ou_servico,
                        quantidade, unidade_medida,
                        valor_unitario_estimado, valor_total_estimado,
                        item_categoria, ncm_nbs_codigo, ncm_nbs_descricao,
                        criterio_julgamento, situacao,
                        tipo_beneficio,
                        dados_completos_json,
                        data_inclusao, data_atualizacao
                    ) VALUES (
                        %s, %s, %s, %s, %s, %s, %s, %s, %s,
                        %s, %s, %s, %s, %s, %s, %s, %s, %s
                    )
                """
                
                material_servico = item.get("materialOuServico", "M")
                tipo_item = "MATERIAL" if material_servico == "M" else "SERVICO"
                
                valores_item = (
                    licitacao_uuid, item.get("numeroItem"), item.get("descricao"),
                    tipo_item, material_servico,
                    item.get("quantidade"), item.get("unidadeMedida"),
                    item.get("valorUnitarioEstimado"), item.get("valorTotal"),
                    item.get("itemCategoriaNome"), item.get("ncmNbsCodigo"),
                    item.get("ncmNbsDescricao"), item.get("criterioJulgamentoNome"),
                    item.get("situacaoCompraItemNome"), item.get("tipoBeneficioNome"),
                    json.dumps(item, ensure_ascii=False),
                    item.get("dataInclusao"), item.get("dataAtualizacao")
                )
                
                cursor.execute(sql_item, valores_item)
            
            # Inserir arquivos
            for arquivo in arquivos:
                sql_arquivo = """
                    INSERT INTO licitacao_arquivos (
                        licitacao_id, sequencial_documento, titulo,
                        tipo_documento, tipo_documento_id, tipo_documento_descricao,
                        uri_original, url_download,
                        status_ativo, data_publicacao,
                        ano_compra, cnpj_orgao, sequencial_compra,
                        dados_completos_json, data_insercao
                    ) VALUES (
                        %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
                    )
                """
                
                valores_arquivo = (
                    licitacao_uuid, arquivo.get("sequencialDocumento"), arquivo.get("titulo"),
                    arquivo.get("tipoDocumentoNome"), arquivo.get("tipoDocumentoId"),
                    arquivo.get("tipoDocumentoDescricao"), arquivo.get("uri"), arquivo.get("url"),
                    arquivo.get("statusAtivo", True), arquivo.get("dataPublicacaoPncp"),
                    arquivo.get("anoCompra"), arquivo.get("cnpj"), arquivo.get("sequencialCompra"),
                    json.dumps(arquivo, ensure_ascii=False), datetime.now()
                )
                
                cursor.execute(sql_arquivo, valores_arquivo)
            
            # Inserir histórico
            for hist in historico:
                sql_historico = """
                    INSERT INTO licitacao_historico (
                        licitacao_id, tipo_log, tipo_log_nome,
                        categoria_log, categoria_log_nome,
                        descricao, justificativa,
                        usuario_nome, item_numero, documento_sequencial,
                        compra_sequencial, compra_orgao_cnpj, compra_ano,
                        dados_completos_json, data_inclusao
                    ) VALUES (
                        %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
                    )
                """
                
                valores_historico = (
                    licitacao_uuid, hist.get("tipoLogManutencao"), hist.get("tipoLogManutencaoNome"),
                    hist.get("categoriaLogManutencao"), hist.get("categoriaLogManutencaoNome"),
                    f"{hist.get('tipoLogManutencaoNome', '')} - {hist.get('categoriaLogManutencaoNome', '')}",
                    hist.get("justificativa"), hist.get("usuarioNome"), hist.get("itemNumero"),
                    hist.get("documentoSequencial"), hist.get("compraSequencial"),
                    hist.get("compraOrgaoCnpj"), hist.get("compraAno"),
                    json.dumps(hist, ensure_ascii=False), hist.get("logManutencaoDataInclusao")
                )
                
                cursor.execute(sql_historico, valores_historico)
            
            conn.commit()
            cursor.close()
            conn.close()
            return True
            
        except Error as e:
            logger.error(f"Erro MySQL ao salvar licitação: {e}")
            if conn:
                conn.rollback()
                conn.close()
            return False
        except Exception as e:
            logger.error(f"Erro ao salvar licitação: {e}")
            if conn:
                conn.rollback()
                conn.close()
            return False

# ============================================================================
# FUNÇÃO PARA PROCESSAR UMA LICITAÇÃO
# ============================================================================

def processar_licitacao(lic, uf):
    """Processa uma única licitação"""
    try:
        cnpj = lic.get("orgao_cnpj")
        ano = lic.get("ano")
        sequencial = lic.get("numero_sequencial")
        
        if not all([cnpj, ano, sequencial]):
            return False, "dados_invalidos"
        
        detalhes = buscar_detalhes_licitacao(cnpj, ano, sequencial)
        dados_para_salvar = detalhes if detalhes else lic
        
        itens = buscar_itens_licitacao(cnpj, ano, sequencial)
        arquivos = buscar_arquivos_licitacao(cnpj, ano, sequencial)
        historico = buscar_historico_licitacao(cnpj, ano, sequencial)
        
        if arquivos:
            titulo = dados_para_salvar.get("objetoCompra") or dados_para_salvar.get("title") or "Sem_Titulo"
            organizar_arquivos_licitacao(None, cnpj, ano, sequencial, titulo[:50], arquivos)
        
        sucesso = popular_licitacao_no_banco(dados_para_salvar, itens, arquivos, historico)
        
        return sucesso, "ok" if sucesso else "erro_salvar"
            
    except Exception as e:
        logger.error(f"Erro ao processar licitação: {e}")
        return False, str(e)

# ============================================================================
# FUNÇÃO PARA COLETAR DADOS DE UM ESTADO
# ============================================================================

def coletar_estado(uf, estatistica):
    """Coleta todas as licitações de um estado"""
    logger.info(f"[{uf}] Iniciando coleta...")
    
    salvos_total = 0
    base_url = "https://pncp.gov.br/api/search/"
    
    params = {
        "tipos_documento": "edital",
        "ordenacao": "-data",
        "tam_pagina": 100,
        "status": "recebendo_proposta",
        "ufs": uf,
        "pagina": 1
    }
    
    total_registros = estatistica['previsto']
    total_paginas = min((total_registros // 100) + 1, 100)
    logger.info(f"[{uf}] Total estimado: {total_registros} licitações em ~{total_paginas} páginas")
    
    for pagina in range(1, total_paginas + 1):
        params["pagina"] = pagina
        
        try:
            response = requests.get(base_url, params=params, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                items = data.get("items", [])
                
                if not items:
                    break
                
                salvos = 0
                erros = 0
                
                futures = []
                for lic in items:
                    future = licitacao_executor.submit(processar_licitacao, lic, uf)
                    futures.append(future)
                
                for future in as_completed(futures):
                    try:
                        sucesso, _ = future.result()
                        if sucesso:
                            salvos += 1
                        else:
                            erros += 1
                    except Exception:
                        erros += 1
                
                salvos_total += salvos
                logger.info(f"[{uf}] Página {pagina}/{total_paginas}: {salvos} OK, {erros} erros")
                time_module.sleep(DELAY_ENTRE_REQUESTS)
            else:
                logger.warning(f"[{uf}] Erro {response.status_code}")
                break
                
        except Exception as e:
            logger.error(f"[{uf}] Erro: {e}")
            break
    
    estatistica['coletados'] = salvos_total
    estatistica['completo'] = True
    
    logger.info(f"[{uf}] Concluído: {salvos_total} licitações salvas")
    return salvos_total

# ============================================================================
# FUNÇÃO PRINCIPAL DO JOB
# ============================================================================

def executar_coleta():
    """Função principal que executa a coleta completa"""
    global download_executor, licitacao_executor, FONTE_PNCP_ID
    
    tempo_inicio = time_module.time()
    logger.info("="*70)
    logger.info("INICIANDO COLETA AUTOMÁTICA DE LICITAÇÕES PNCP")
    logger.info("="*70)
    
    try:
        # Verificar se coleta de hoje já foi feita
        if verificar_coleta_hoje_completa():
            logger.info("Coleta de hoje já foi realizada. Aguardando próxima execução.")
            return
        
        # Verificar banco de dados com retry (tenta a cada 10 minutos por 6 horas)
        tentativas_max = 36  # 36 tentativas × 10 minutos = 6 horas
        for tentativa in range(1, tentativas_max + 1):
            if verificar_banco_existe():
                break
            
            if tentativa < tentativas_max:
                tempo_restante = (tentativas_max - tentativa) * 10
                logger.warning(f"MySQL/Internet indisponível. Tentativa {tentativa}/{tentativas_max}. Aguardando 10 minutos... (Tempo restante: ~{tempo_restante} minutos)")
                time_module.sleep(600)  # 10 minutos = 600 segundos
            else:
                logger.error(f"MySQL/Internet indisponível após 6 horas de tentativas. Abortando coleta de hoje. Aguardando próxima execução agendada às {HORARIO_COLETA}.")
                return
        
        # Inicializar pools de threads
        if FAZER_DOWNLOAD_ARQUIVOS:
            download_executor = ThreadPoolExecutor(max_workers=MAX_DOWNLOAD_THREADS, thread_name_prefix="Download")
            logger.info(f"Pool de downloads: {MAX_DOWNLOAD_THREADS} threads")
        
        licitacao_executor = ThreadPoolExecutor(max_workers=MAX_LICITACAO_THREADS, thread_name_prefix="Licitacao")
        logger.info(f"Pool de licitações: {MAX_LICITACAO_THREADS} threads")
        
        # Buscar estatísticas prévias
        logger.info("Verificando quantidade de licitações por UF...")
        base_url = "https://pncp.gov.br/api/search/"
        estatisticas_previas = {}
        
        # Tentar buscar estatísticas com retry
        for uf in TODAS_UFS:
            params = {
                "tipos_documento": "edital",
                "ordenacao": "-data",
                "tam_pagina": 1,
                "status": "recebendo_proposta",
                "ufs": uf,
                "pagina": 1
            }
            
            # Tentar 3 vezes por UF
            for tentativa_uf in range(3):
                try:
                    response = requests.get(base_url, params=params, timeout=30)
                    if response.status_code == 200:
                        data = response.json()
                        total = data.get("total", 0)
                        estatisticas_previas[uf] = total
                        logger.info(f"{uf}: {total} licitações")
                        break
                    else:
                        if tentativa_uf < 2:
                            time_module.sleep(5)
                        else:
                            estatisticas_previas[uf] = 0
                            logger.warning(f"{uf}: Falha ao buscar estatísticas, continuando com 0")
                except Exception as e:
                    if tentativa_uf < 2:
                        logger.warning(f"Erro ao verificar {uf} (tentativa {tentativa_uf + 1}/3): {e}")
                        time_module.sleep(5)
                    else:
                        logger.error(f"Erro ao verificar {uf} após 3 tentativas: {e}")
                        estatisticas_previas[uf] = 0
            
            time_module.sleep(0.2)
        
        total_geral = sum(estatisticas_previas.values())
        logger.info(f"Total geral: {total_geral} licitações")
        
        # Coleta paralela
        logger.info("="*70)
        logger.info(f"INICIANDO COLETA PARALELA - {MAX_THREADS} estados simultâneos")
        logger.info("="*70)
        
        estatisticas = {uf: {'previsto': estatisticas_previas.get(uf, 0), 'coletados': 0, 'completo': False} 
                        for uf in TODAS_UFS}
        
        total_salvas = 0
        
        with ThreadPoolExecutor(max_workers=MAX_THREADS, thread_name_prefix="Estado") as executor:
            futures = {executor.submit(coletar_estado, uf, estatisticas[uf]): uf for uf in TODAS_UFS}
            
            for future in as_completed(futures):
                uf = futures[future]
                try:
                    salvos_uf = future.result()
                    total_salvas += salvos_uf
                except Exception as e:
                    logger.error(f"Erro ao coletar {uf}: {e}")
        
        # Aguardar downloads
        if download_executor:
            logger.info("Aguardando downloads finalizarem...")
            download_executor.shutdown(wait=True)
            logger.info("Downloads concluídos!")
        
        # Encerrar licitacoes
        licitacao_executor.shutdown(wait=True)
        
        # Atualizar licitações encerradas
        logger.info("Atualizando status de licitações encerradas...")
        try:
            conn = criar_conexao()
            if conn:
                cursor = conn.cursor()
                sql_encerrar = """
                    UPDATE licitacoes 
                    SET situacao = 'Encerrada', status = 'ENCERRADA' 
                    WHERE situacao IN ('Divulgada no PNCP', 'Aberta', 'Recebendo Proposta', 'Aceite de Propostas', 'Publicada', 'Aguardando Propostas', 'Em Disputa')
                    AND (
                        data_encerramento_proposta < NOW()
                        OR data_atualizacao < DATE_SUB(NOW(), INTERVAL 2 DAY)
                    )
                """
                cursor.execute(sql_encerrar)
                licitacoes_encerradas = cursor.rowcount
                conn.commit()
                logger.info(f"{licitacoes_encerradas} licitações marcadas como encerradas")
                cursor.close()
                conn.close()
        except Exception as e:
            logger.error(f"Erro ao atualizar status: {e}")
        
        # Relatório final
        tempo_total = time_module.time() - tempo_inicio
        minutos = int(tempo_total // 60)
        segundos = int(tempo_total % 60)
        
        logger.info("="*70)
        logger.info("COLETA CONCLUÍDA!")
        logger.info(f"Tempo total: {minutos}min {segundos}s")
        logger.info(f"Total salvo: {total_salvas} licitações")
        logger.info("="*70)
        
        # Resumo por estado
        for uf in sorted(TODAS_UFS):
            est = estatisticas[uf]
            status = "OK" if est['completo'] else "ERRO"
            logger.info(f"{uf}: {est['coletados']}/{est['previsto']} - {status}")
        
        logger.info("="*70)
        
    except KeyboardInterrupt:
        logger.info("Coleta interrompida pelo usuário (Ctrl+C)")
        raise  # Re-lançar para parar o worker completamente
    except Exception as e:
        logger.error(f"Erro fatal na coleta: {e}", exc_info=True)
        logger.info("Worker continuará rodando e aguardará próxima execução agendada.")
    finally:
        # Limpar pools de forma segura
        try:
            if download_executor:
                download_executor.shutdown(wait=False)
        except Exception as e:
            logger.error(f"Erro ao encerrar download_executor: {e}")
        
        try:
            if licitacao_executor:
                licitacao_executor.shutdown(wait=False)
        except Exception as e:
            logger.error(f"Erro ao encerrar licitacao_executor: {e}")

# ============================================================================
# CONFIGURAÇÃO DO AGENDAMENTO
# ============================================================================

def job():
    """Job agendado que será executado diariamente"""
    global job_em_execucao
    
    # Verificar se já tem coleta rodando
    with job_lock:
        if job_em_execucao:
            logger.warning("Coleta anterior ainda em execução. Pulando execução agendada.")
            return
        job_em_execucao = True
    
    try:
        logger.info(f"Job iniciado às {datetime.now().strftime('%H:%M:%S')}")
        executar_coleta()
        logger.info(f"Job concluído às {datetime.now().strftime('%H:%M:%S')}")
    except KeyboardInterrupt:
        logger.info("Job interrompido manualmente")
        raise  # Re-lançar para permitir parada do worker
    except Exception as e:
        logger.error(f"Erro inesperado no job: {e}", exc_info=True)
        logger.info("Job finalizado com erro. Worker continuará rodando.")
    finally:
        # Garantir que flag seja sempre resetada
        try:
            with job_lock:
                job_em_execucao = False
        except Exception as e:
            logger.error(f"Erro ao resetar flag de execução: {e}")
            job_em_execucao = False  # Forçar reset mesmo sem lock

# ============================================================================
# MAIN - LOOP PRINCIPAL DO WORKER
# ============================================================================

if __name__ == "__main__":
    logger.info("="*70)
    logger.info("WORKER DE LICITAÇÕES INICIADO")
    logger.info(f"Horário de coleta: {HORARIO_COLETA}")
    logger.info(f"Próxima execução: hoje às {HORARIO_COLETA}")
    logger.info("="*70)
    
    # Agendar job diário
    schedule.every().day.at(HORARIO_COLETA).do(job)
    
    # Verificar se deve executar imediatamente (modo manual)
    if len(sys.argv) > 1 and sys.argv[1] == "--now":
        logger.info("Modo manual: executando coleta imediatamente...")
        job()
    
    # Loop principal
    logger.info("Worker em execução. Aguardando horário agendado...")
    logger.info("Pressione Ctrl+C para parar o worker")
    
    try:
        while True:
            schedule.run_pending()
            time_module.sleep(60)  # Verifica a cada 1 minuto
    except KeyboardInterrupt:
        logger.info("Worker interrompido pelo usuário")
        if download_executor:
            download_executor.shutdown(wait=False)
        if licitacao_executor:
            licitacao_executor.shutdown(wait=False)
        logger.info("Worker finalizado")
