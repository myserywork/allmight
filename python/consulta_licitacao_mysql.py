# -*- coding: utf-8 -*-
import sys
import io

# Configurar encoding UTF-8 para o terminal Windows
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

import requests
import json
from datetime import datetime
import time
import os
import mysql.connector
from mysql.connector import Error
import glob
from concurrent.futures import ThreadPoolExecutor, as_completed
import threading
import uuid

# ============================================================================
# CONFIGURAÇÕES DO MYSQL
# ============================================================================
MYSQL_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',  # senha em branco
    'database': 'allmight',
    'charset': 'utf8mb4',
    'collation': 'utf8mb4_unicode_ci',
    'autocommit': False
}

# ============================================================================
# CONFIGURAÇÕES DA COLETA
# ============================================================================
tempo_inicio = time.time()
DELAY_ENTRE_REQUESTS = 0.1  # segundos entre cada request
MAX_THREADS = 20  # Número máximo de threads paralelas
MAX_DOWNLOAD_THREADS = 30  # Threads dedicadas para downloads
MAX_LICITACAO_THREADS = 10  # Threads para processar licitações dentro de um estado
FAZER_DOWNLOAD_ARQUIVOS = True  # True = baixar arquivos, False = só salvar info no banco
PASTA_DOWNLOADS = "downloads_licitacoes"  # Pasta raiz para downloads
DOWNLOAD_TIMEOUT = 30  # Timeout de download em segundos

# Lock para sincronizar escritas no banco
db_lock = threading.Lock()

# Pool de threads dedicado para downloads
download_executor = None  # Será inicializado depois

# Pool de threads para processar licitações
licitacao_executor = None  # Será inicializado depois

# ID da fonte PNCP (será buscado do banco)
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
        print(f"❌ Erro ao conectar ao MySQL: {e}")
        return None

def verificar_banco_existe():
    """Verifica se o banco e a fonte PNCP existem"""
    global FONTE_PNCP_ID
    
    print(f"\n{'='*70}")
    print("VERIFICANDO CONEXÃO COM MYSQL")
    print(f"{'='*70}")
    
    try:
        conn = criar_conexao()
        if not conn:
            print("❌ Não foi possível conectar ao MySQL!")
            print("\n📋 INSTRUÇÕES:")
            print("1. Certifique-se que o XAMPP está rodando")
            print("2. Abra o phpMyAdmin: http://localhost/phpmyadmin")
            print("3. Crie o banco 'allmight'")
            print("4. Execute o arquivo 'schema_allmight_mysql.sql'")
            return False
        
        cursor = conn.cursor(dictionary=True)
        
        # Verificar se as tabelas existem
        cursor.execute("SHOW TABLES LIKE 'licitacoes'")
        if not cursor.fetchone():
            print("❌ Tabela 'licitacoes' não encontrada!")
            print("\n📋 Execute o arquivo 'schema_allmight_mysql.sql' no banco 'allmight'")
            conn.close()
            return False
        
        # Buscar ID da fonte PNCP
        cursor.execute("SELECT id FROM fontes_licitacao WHERE tipo_portal = 'PNCP' LIMIT 1")
        fonte = cursor.fetchone()
        
        if fonte:
            FONTE_PNCP_ID = fonte['id']
            print(f"✓ Conectado ao MySQL - Banco: allmight")
            print(f"✓ Fonte PNCP encontrada - ID: {FONTE_PNCP_ID}")
        else:
            print("⚠️  Fonte PNCP não encontrada! Verifique se o schema foi executado.")
            conn.close()
            return False
        
        cursor.close()
        conn.close()
        print(f"{'='*70}")
        return True
        
    except Error as e:
        print(f"❌ Erro: {e}")
        return False

if not verificar_banco_existe():
    print("\n❌ Não é possível continuar sem o banco configurado.")
    exit(1)

# ============================================================================
# VERIFICAR COLETA DE HOJE
# ============================================================================
hoje = datetime.now().strftime("%Y%m%d")
arquivos_hoje = glob.glob(f"licitacoes_completo_{hoje}_*.json")

if arquivos_hoje:
    print("="*70)
    print("⚠️  ATENÇÃO: Já existe uma coleta de hoje!")
    print("="*70)
    for arq in arquivos_hoje:
        tamanho = os.path.getsize(arq) / (1024 * 1024)  # MB
        print(f"Arquivo: {arq} ({tamanho:.2f} MB)")
    
    resposta = input("\nDeseja fazer uma nova atualização mesmo assim? (s/n): ").lower()
    if resposta != 's':
        print("\n✓ Execução cancelada pelo usuário.")
        exit(0)
    print("\n✓ Continuando com nova coleta...\n")

# ============================================================================
# INICIALIZAR POOL DE THREADS PARA DOWNLOADS E LICITAÇÕES
# ============================================================================
if FAZER_DOWNLOAD_ARQUIVOS:
    download_executor = ThreadPoolExecutor(max_workers=MAX_DOWNLOAD_THREADS, thread_name_prefix="Download")
    print(f"✓ Pool de downloads inicializado: {MAX_DOWNLOAD_THREADS} threads paralelas")

licitacao_executor = ThreadPoolExecutor(max_workers=MAX_LICITACAO_THREADS, thread_name_prefix="Licitacao")
print(f"✓ Pool de processamento de licitações inicializado: {MAX_LICITACAO_THREADS} threads paralelas")

# ============================================================================
# FUNÇÕES PARA BUSCAR DADOS DETALHADOS NA API
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
            params = {
                "pagina": pagina,
                "tamanhoPagina": 100
            }
            
            response = requests.get(url, params=params, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                
                if isinstance(data, list):
                    if not data:  # Lista vazia, acabaram os itens
                        break
                    todos_itens.extend(data)
                    pagina += 1
                else:
                    break
                    
            elif response.status_code == 404:
                # Não tem itens
                break
            elif response.status_code == 204:
                # Sem conteúdo (página vazia) - normal
                break
            else:
                print(f"  ⚠️  Erro {response.status_code} ao buscar itens")
                break
                
            time.sleep(DELAY_ENTRE_REQUESTS)
            
        except Exception as e:
            print(f"  ⚠️  Erro ao buscar itens: {e}")
            break
    
    return todos_itens


def buscar_arquivos_licitacao(cnpj, ano, sequencial):
    """Busca todos os arquivos de uma licitação"""
    url = f"https://pncp.gov.br/api/pncp/v1/orgaos/{cnpj}/compras/{ano}/{sequencial}/arquivos"
    
    todos_arquivos = []
    pagina = 1
    
    while True:
        try:
            params = {
                "pagina": pagina,
                "tamanhoPagina": 100
            }
            
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
                    
            elif response.status_code == 404:
                break
            elif response.status_code == 204:
                # Sem conteúdo (página vazia) - normal
                break
            else:
                print(f"  ⚠️  Erro {response.status_code} ao buscar arquivos")
                break
                
            time.sleep(DELAY_ENTRE_REQUESTS)
            
        except Exception as e:
            print(f"  ⚠️  Erro ao buscar arquivos: {e}")
            break
    
    return todos_arquivos


def buscar_historico_licitacao(cnpj, ano, sequencial):
    """Busca todo o histórico de alterações de uma licitação"""
    url = f"https://pncp.gov.br/api/pncp/v1/orgaos/{cnpj}/compras/{ano}/{sequencial}/historicos"
    
    todo_historico = []
    pagina = 1
    
    while True:
        try:
            params = {
                "pagina": pagina,
                "tamanhoPagina": 100
            }
            
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
                    
            elif response.status_code == 404:
                break
            elif response.status_code == 204:
                # Sem conteúdo (página vazia) - normal
                break
            else:
                print(f"  ⚠️  Erro {response.status_code} ao buscar histórico")
                break
                
            time.sleep(DELAY_ENTRE_REQUESTS)
            
        except Exception as e:
            print(f"  ⚠️  Erro ao buscar histórico: {e}")
            break
    
    return todo_historico


def sanitizar_nome_arquivo(nome):
    """Remove caracteres inválidos de nomes de arquivo"""
    import re
    # Remove caracteres inválidos para Windows/Linux
    nome_limpo = re.sub(r'[<>:"/\\|?*]', '_', nome)
    # Limita tamanho
    if len(nome_limpo) > 200:
        nome_limpo = nome_limpo[:200]
    return nome_limpo


def baixar_arquivo(url, caminho_destino):
    """Baixa um arquivo da URL para o caminho destino"""
    try:
        # Criar pasta se não existir
        os.makedirs(os.path.dirname(caminho_destino), exist_ok=True)
        
        # Baixar arquivo com streaming
        response = requests.get(url, timeout=DOWNLOAD_TIMEOUT, stream=True)
        response.raise_for_status()
        
        # Salvar arquivo em chunks
        with open(caminho_destino, 'wb') as f:
            for chunk in response.iter_content(chunk_size=65536):  # 64KB chunks
                if chunk:
                    f.write(chunk)
        
        return True, os.path.getsize(caminho_destino)
        
    except Exception as e:
        return False, str(e)


def organizar_arquivos_licitacao(licitacao_id, cnpj, ano, sequencial, titulo, arquivos):
    """Organiza e baixa arquivos de uma licitação em pastas categorizadas"""
    
    if not FAZER_DOWNLOAD_ARQUIVOS or not arquivos:
        return
    
    # Criar pasta base da licitação: CNPJ/ANO/SEQUENCIAL_TITULO
    titulo_limpo = sanitizar_nome_arquivo(titulo)
    pasta_base = os.path.join(PASTA_DOWNLOADS, cnpj, str(ano), f"{sequencial}_{titulo_limpo}")
    
    # Mapeamento de categorias
    categorias = {
        "edital": "editais",
        "ata": "atas",
        "termo": "termos",
        "aviso": "avisos",
        "anexo": "anexos",
    }
    
    # Agendar downloads em paralelo
    futures = []
    
    for arquivo in arquivos:
        url_download = arquivo.get("url")
        tipo_doc = arquivo.get("tipoDocumentoNome", "outros").lower()
        nome_arquivo = arquivo.get("titulo", f"arquivo_{arquivo.get('sequencialDocumento', 'sem_seq')}")
        nome_arquivo = sanitizar_nome_arquivo(nome_arquivo)
        
        # Determinar categoria
        categoria = "outros"
        for chave, pasta in categorias.items():
            if chave in tipo_doc:
                categoria = pasta
                break
        
        # Caminho completo
        pasta_categoria = os.path.join(pasta_base, categoria)
        caminho_arquivo = os.path.join(pasta_categoria, nome_arquivo)
        
        # Se já existe, pular
        if os.path.exists(caminho_arquivo):
            continue
        
        # Agendar download
        if url_download and download_executor:
            future = download_executor.submit(baixar_arquivo, url_download, caminho_arquivo)
            futures.append((future, nome_arquivo))
    
    # Não esperar pelos downloads (assíncrono)
    # Os downloads continuarão em background


# ============================================================================
# FUNÇÃO PARA SALVAR LICITAÇÃO NO MYSQL
# ============================================================================

def popular_licitacao_no_banco(licitacao, itens, arquivos, historico):
    """Salva licitação completa no banco MySQL"""
    
    with db_lock:  # Sincronizar acesso ao banco
        conn = criar_conexao()
        if not conn:
            return False
        
        try:
            cursor = conn.cursor()
            
            # Detectar se é da API de busca ou detalhes
            # API de busca tem: orgao_cnpj, ano, numero_sequencial
            # API de detalhes tem: orgaoEntidade.cnpj, anoCompra, sequencialCompra
            if "orgao_cnpj" in licitacao:
                # Dados da API de busca
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
                # Dados da API de detalhes
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
            
            # ID externo (único no portal)
            id_externo = f"{cnpj}_{ano}_{sequencial}"
            
            # URLs
            url_navegador = f"https://pncp.gov.br/app/editais/{cnpj}/{ano}/{sequencial}"
            
            # Verificar se a licitação já existe no banco
            cursor.execute("SELECT id FROM licitacoes WHERE id_externo = %s", (id_externo,))
            licitacao_existente = cursor.fetchone()
            
            if licitacao_existente:
                # Usar ID existente
                licitacao_uuid = licitacao_existente[0]
                
                # Limpar dados antigos (serão reinseridos com dados atualizados)
                cursor.execute("DELETE FROM licitacao_itens WHERE licitacao_id = %s", (licitacao_uuid,))
                cursor.execute("DELETE FROM licitacao_arquivos WHERE licitacao_id = %s", (licitacao_uuid,))
                cursor.execute("DELETE FROM licitacao_historico WHERE licitacao_id = %s", (licitacao_uuid,))
            else:
                # Gerar novo ID
                licitacao_uuid = str(uuid.uuid4())
            
            # Inserir licitação principal
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
            
            # Mapear situação do PNCP para status do sistema
            # Situações da API: "Recebendo Proposta", "Aceite de Propostas", etc.
            # Dashboard usa: 'Aberta', 'Encerrada', 'Cancelada'
            status_mapeado = "Aberta"  # Padrão
            if situacao_nome:
                situacao_lower = situacao_nome.lower()
                if "recebendo" in situacao_lower or "aceite" in situacao_lower or "proposta" in situacao_lower:
                    status_mapeado = "Aberta"
                elif "finalizada" in situacao_lower or "encerrada" in situacao_lower or "homolog" in situacao_lower:
                    status_mapeado = "Encerrada"
                elif "suspensa" in situacao_lower or "cancelada" in situacao_lower:
                    status_mapeado = "Cancelada"
            
            valores_licitacao = (
                licitacao_uuid,
                FONTE_PNCP_ID,
                id_externo,
                numero_controle,
                numero_edital,
                titulo[:500] if titulo else "Sem título",
                descricao,
                titulo,
                cnpj,
                orgao_nome,
                unidade_nome,
                uf,
                municipio_nome,
                modalidade_nome,
                modalidade_id,
                status_mapeado,  # Coluna 'situacao' usa status mapeado (ex: "Aberta")
                situacao_id,
                status_mapeado,  # Coluna 'status' usa mesmo valor
                data_publicacao,
                data_inicio,
                data_fim,
                valor_global,
                url_navegador,
                len(arquivos) > 0,
                len(arquivos) > 0,
                json.dumps(licitacao, ensure_ascii=False),
                True,
                datetime.now(),
                datetime.now()
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
                        %s, %s, %s,
                        %s, %s,
                        %s, %s,
                        %s, %s,
                        %s, %s, %s,
                        %s, %s,
                        %s,
                        %s,
                        %s, %s
                    )
                """
                
                # Determinar tipo
                material_servico = item.get("materialOuServico", "M")
                tipo_item = "MATERIAL" if material_servico == "M" else "SERVICO"
                
                valores_item = (
                    licitacao_uuid,
                    item.get("numeroItem"),
                    item.get("descricao"),
                    tipo_item,
                    material_servico,
                    item.get("quantidade"),
                    item.get("unidadeMedida"),
                    item.get("valorUnitarioEstimado"),
                    item.get("valorTotal"),
                    item.get("itemCategoriaNome"),
                    item.get("ncmNbsCodigo"),
                    item.get("ncmNbsDescricao"),
                    item.get("criterioJulgamentoNome"),
                    item.get("situacaoCompraItemNome"),
                    item.get("tipoBeneficioNome"),
                    json.dumps(item, ensure_ascii=False),
                    item.get("dataInclusao"),
                    item.get("dataAtualizacao")
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
                        dados_completos_json,
                        data_insercao
                    ) VALUES (
                        %s, %s, %s,
                        %s, %s, %s,
                        %s, %s,
                        %s, %s,
                        %s, %s, %s,
                        %s,
                        %s
                    )
                """
                
                valores_arquivo = (
                    licitacao_uuid,
                    arquivo.get("sequencialDocumento"),
                    arquivo.get("titulo"),
                    arquivo.get("tipoDocumentoNome"),
                    arquivo.get("tipoDocumentoId"),
                    arquivo.get("tipoDocumentoDescricao"),
                    arquivo.get("uri"),
                    arquivo.get("url"),
                    arquivo.get("statusAtivo", True),
                    arquivo.get("dataPublicacaoPncp"),
                    arquivo.get("anoCompra"),
                    arquivo.get("cnpj"),
                    arquivo.get("sequencialCompra"),
                    json.dumps(arquivo, ensure_ascii=False),
                    datetime.now()
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
                        dados_completos_json,
                        data_inclusao
                    ) VALUES (
                        %s, %s, %s,
                        %s, %s,
                        %s, %s,
                        %s, %s, %s,
                        %s, %s, %s,
                        %s,
                        %s
                    )
                """
                
                valores_historico = (
                    licitacao_uuid,
                    hist.get("tipoLogManutencao"),
                    hist.get("tipoLogManutencaoNome"),
                    hist.get("categoriaLogManutencao"),
                    hist.get("categoriaLogManutencaoNome"),
                    f"{hist.get('tipoLogManutencaoNome', '')} - {hist.get('categoriaLogManutencaoNome', '')}",
                    hist.get("justificativa"),
                    hist.get("usuarioNome"),
                    hist.get("itemNumero"),
                    hist.get("documentoSequencial"),
                    hist.get("compraSequencial"),
                    hist.get("compraOrgaoCnpj"),
                    hist.get("compraAno"),
                    json.dumps(hist, ensure_ascii=False),
                    hist.get("logManutencaoDataInclusao")
                )
                
                cursor.execute(sql_historico, valores_historico)
            
            conn.commit()
            cursor.close()
            conn.close()
            
            return True
            
        except Error as e:
            print(f"\n❌ Erro MySQL: {e}")
            if conn:
                conn.rollback()
                conn.close()
            return False
        except Exception as e:
            print(f"\n❌ Erro ao salvar: {e}")
            if conn:
                conn.rollback()
                conn.close()
            return False


# ============================================================================
# BUSCAR LICITAÇÕES
# ============================================================================

base_url = "https://pncp.gov.br/api/search/"

# ============================================================================
# SELEÇÃO DE ESTADOS
# ============================================================================
print("\n" + "="*70)
print("SELEÇÃO DE ESTADOS PARA COLETA")
print("="*70)
print("\nEscolha a abrangência da coleta:")
print("  1 - Apenas DF (RÁPIDO - para testes)")
print("  2 - Todos os estados (COMPLETO - ~20-30 minutos)")
print("="*70)

escolha = input("\nDigite sua escolha (1 ou 2) [padrão: 1]: ").strip()

# Lista de todas as UFs do Brasil
TODAS_UFS = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 
             'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 
             'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']

# Definir UFs baseado na escolha
if escolha == "2":
    UFS = TODAS_UFS
    print(f"\n✓ Modo COMPLETO selecionado: {len(UFS)} estados")
else:
    UFS = ['DF']
    print(f"\n✓ Modo TESTE selecionado: apenas DF (Distrito Federal)")

print("="*70)

print("\n" + "="*70)
print("VERIFICANDO QUANTIDADE DE LICITAÇÕES POR UF")
print("="*70)

estatisticas_previas = {}
ufs_problematicas = []

for uf in UFS:
    params = {
        "tipos_documento": "edital",
        "ordenacao": "-data",
        "tam_pagina": 1,
        "status": "recebendo_proposta",
        "ufs": uf,
        "pagina": 1
    }
    
    try:
        response = requests.get(base_url, params=params, timeout=30)
        if response.status_code == 200:
            data = response.json()
            total = data.get("total", 0)
            estatisticas_previas[uf] = total
            
            status = "⚠️  ATENÇÃO" if total > 10000 else "✓"
            print(f"{status} {uf}: {total:>6} licitações")
            
            if total > 10000:
                ufs_problematicas.append((uf, total))
        else:
            print(f"✗ {uf}: Erro {response.status_code}")
            estatisticas_previas[uf] = 0
            
    except Exception as e:
        print(f"✗ {uf}: Erro - {e}")
        estatisticas_previas[uf] = 0
    
    time.sleep(0.2)

print(f"\n{'='*70}")
print(f"TOTAL GERAL: {sum(estatisticas_previas.values())} licitações")

if ufs_problematicas:
    print(f"\n⚠️  ESTADOS COM MAIS DE 10.000 ITENS:")
    for uf, total in ufs_problematicas:
        print(f"   {uf}: {total} (pode não pegar tudo)")
else:
    print(f"\n✓ Todos os estados têm menos de 10.000 itens!")

print("="*70)

# ============================================================================
# FUNÇÃO PARA PROCESSAR UMA ÚNICA LICITAÇÃO
# ============================================================================

def processar_licitacao(lic, uf):
    """Processa uma única licitação (busca detalhes, itens, arquivos e salva no banco)"""
    try:
        # Extrair dados básicos da API de busca
        cnpj = lic.get("orgao_cnpj")
        ano = lic.get("ano")
        sequencial = lic.get("numero_sequencial")
        
        if not all([cnpj, ano, sequencial]):
            return False, "dados_invalidos"
        
        # Buscar dados detalhados da licitação
        detalhes = buscar_detalhes_licitacao(cnpj, ano, sequencial)
        
        # Se não tem detalhes, usar os dados da busca
        dados_para_salvar = detalhes if detalhes else lic
        
        # Buscar itens, arquivos e histórico
        itens = buscar_itens_licitacao(cnpj, ano, sequencial)
        arquivos = buscar_arquivos_licitacao(cnpj, ano, sequencial)
        historico = buscar_historico_licitacao(cnpj, ano, sequencial)
        
        # Organizar downloads (assíncrono)
        if arquivos:
            titulo = dados_para_salvar.get("objetoCompra") or dados_para_salvar.get("title") or "Sem_Titulo"
            organizar_arquivos_licitacao(
                None,  # ID será gerado ao salvar
                cnpj,
                ano,
                sequencial,
                titulo[:50],
                arquivos
            )
        
        # Salvar no banco
        sucesso = popular_licitacao_no_banco(dados_para_salvar, itens, arquivos, historico)
        
        if sucesso:
            return True, detalhes
        else:
            return False, "erro_salvar"
            
    except Exception as e:
        return False, str(e)


# ============================================================================
# FUNÇÃO PARA COLETAR DADOS DE UM ESTADO (THREAD)
# ============================================================================

def coletar_estado(uf, estatistica):
    """Coleta todas as licitações de um estado"""
    print(f"\n🔄 [{uf}] Iniciando coleta...")
    
    licitacoes_estado = []
    pagina = 1
    total_paginas = 0
    
    params = {
        "tipos_documento": "edital",
        "ordenacao": "-data",
        "tam_pagina": 100,  # 100 itens por página
        "status": "recebendo_proposta",
        "ufs": uf,
        "pagina": 1
    }
    
    # Calcular total de páginas
    total_registros = estatistica['previsto']
    total_paginas = min((total_registros // 100) + 1, 100)
    print(f"  [{uf}] Total: {total_registros} licitações em ~{total_paginas} páginas")
    
    for pagina in range(1, total_paginas + 1):
        params["pagina"] = pagina
        
        try:
            response = requests.get(base_url, params=params, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                items = data.get("items", [])
                
                if not items:
                    break
                
                print(f"  [{uf}] Página {pagina}/{total_paginas} - {len(items)} licitações", end="", flush=True)
                
                # Processar licitações em paralelo
                salvos = 0
                erros = 0
                
                # Submeter todas as licitações para processamento paralelo
                futures = []
                for lic in items:
                    future = licitacao_executor.submit(processar_licitacao, lic, uf)
                    futures.append(future)
                
                # Aguardar resultados
                for future in as_completed(futures):
                    try:
                        sucesso, resultado = future.result()
                        if sucesso:
                            licitacoes_estado.append(resultado)
                            salvos += 1
                            print(f"✓", end="", flush=True)
                        else:
                            erros += 1
                            if resultado == "dados_invalidos":
                                print(f"X", end="", flush=True)
                            elif resultado == "erro_salvar":
                                print(f"✗", end="", flush=True)
                            else:
                                print(f"!", end="", flush=True)
                    except Exception as e:
                        print(f"!", end="", flush=True)
                        erros += 1
                
                print(f" -> {salvos} OK, {erros} erros")
                time.sleep(DELAY_ENTRE_REQUESTS)
                
            else:
                print(f"  ✗ [{uf}] Erro {response.status_code}")
                break
                
        except Exception as e:
            print(f"  ✗ [{uf}] Erro: {e}")
            break
    
    # Atualizar estatísticas
    estatistica['coletados'] = len(licitacoes_estado)
    estatistica['completo'] = True
    
    print(f"✓ [{uf}] Concluído: {len(licitacoes_estado)} licitações")
    
    return licitacoes_estado


# ============================================================================
# COLETA PARALELA POR ESTADO
# ============================================================================

print(f"\n{'='*70}")
print(f"INICIANDO COLETA PARALELA")
print(f"Threads: {MAX_THREADS} estados simultâneos")
print(f"Processamento: {MAX_LICITACAO_THREADS} licitações paralelas por estado")
print(f"Download: {MAX_DOWNLOAD_THREADS} arquivos simultâneos")
print(f"{'='*70}\n")

estatisticas = {uf: {'previsto': estatisticas_previas.get(uf, 0), 'coletados': 0, 'completo': False} 
                for uf in UFS}

todas_licitacoes = []

# Executar coleta em paralelo
with ThreadPoolExecutor(max_workers=MAX_THREADS, thread_name_prefix="Estado") as executor:
    futures = {executor.submit(coletar_estado, uf, estatisticas[uf]): uf for uf in UFS}
    
    for future in as_completed(futures):
        uf = futures[future]
        try:
            licitacoes_uf = future.result()
            todas_licitacoes.extend(licitacoes_uf)
        except Exception as e:
            print(f"❌ Erro ao coletar {uf}: {e}")

# ============================================================================
# RELATÓRIO FINAL
# ============================================================================

tempo_total = time.time() - tempo_inicio
minutos = int(tempo_total // 60)
segundos = int(tempo_total % 60)

print(f"\n{'='*70}")
print("COLETA CONCLUÍDA!")
print(f"{'='*70}")
print(f"Tempo total: {minutos}min {segundos}s")
print(f"Total coletado: {len(todas_licitacoes)} licitações")
print(f"\nResumo por Estado:")
print(f"{'UF':<4} {'Previsto':>8} {'Coletado':>10} {'Status':>10}")
print("-" * 70)

for uf in sorted(UFS):
    est = estatisticas[uf]
    status = "✓" if est['completo'] else "✗"
    print(f"{uf:<4} {est['previsto']:>8} {est['coletados']:>10} {status:>10}")

print(f"{'='*70}")

# Salvar JSON de backup
nome_arquivo = f"licitacoes_completo_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
with open(nome_arquivo, 'w', encoding='utf-8') as f:
    json.dump(todas_licitacoes, f, ensure_ascii=False, indent=2)

tamanho_mb = os.path.getsize(nome_arquivo) / (1024 * 1024)
print(f"\n✓ Backup JSON salvo: {nome_arquivo} ({tamanho_mb:.2f} MB)")

# Aguardar downloads finalizarem
if download_executor:
    print(f"\n⏳ Aguardando downloads em background finalizarem...")
    download_executor.shutdown(wait=True)
    print(f"✓ Todos os downloads concluídos!")

# ============================================================================
# MARCAR LICITAÇÕES ENCERRADAS AUTOMATICAMENTE
# ============================================================================
print(f"\n{'='*70}")
print("🔄 ATUALIZANDO STATUS DE LICITAÇÕES ENCERRADAS")
print(f"{'='*70}")

try:
    conn = criar_conexao()
    if conn:
        cursor = conn.cursor()
        
        # Marcar como encerradas as licitações que:
        # 1. Têm data de encerramento de propostas no passado (já fecharam)
        # 2. Ou não foram atualizadas há mais de 2 dias (saíram da API)
        sql_encerrar = """
            UPDATE licitacoes 
            SET situacao = 'Encerrada', 
                status = 'ENCERRADA' 
            WHERE situacao IN ('Divulgada no PNCP', 'Aberta', 'Recebendo Proposta', 'Aceite de Propostas', 'Publicada', 'Aguardando Propostas', 'Em Disputa')
            AND (
                data_encerramento_proposta < NOW()
                OR data_atualizacao < DATE_SUB(NOW(), INTERVAL 2 DAY)
            )
        """
        
        cursor.execute(sql_encerrar)
        licitacoes_encerradas = cursor.rowcount
        conn.commit()
        
        print(f"✓ {licitacoes_encerradas} licitações marcadas como encerradas")
        print(f"  - Critério: data de encerramento passou OU não atualizadas há +2 dias")
        
        cursor.close()
        conn.close()
    else:
        print("⚠️  Não foi possível conectar ao banco para atualizar status")
        
except Exception as e:
    print(f"⚠️  Erro ao atualizar status: {e}")

print(f"{'='*70}")

print(f"\n{'='*70}")
print("🎉 PROCESSO COMPLETO!")
print(f"{'='*70}")
print(f"📊 Banco MySQL: {len(todas_licitacoes)} licitações coletadas")
print(f"💾 Backup JSON: {nome_arquivo}")
print(f"📁 Downloads: {PASTA_DOWNLOADS}/")
print(f"{'='*70}\n")
