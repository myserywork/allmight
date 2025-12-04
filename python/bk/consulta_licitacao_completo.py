import requests
import json
from datetime import datetime
import time
import os
import sqlite3
import glob
from concurrent.futures import ThreadPoolExecutor, as_completed
import threading

# ============================================================================
# CONFIGURAÇÕES
# ============================================================================
tempo_inicio = time.time()
DELAY_ENTRE_REQUESTS = 0.1  # segundos entre cada request (reduzido)
MAX_THREADS = 20  # Número máximo de threads paralelas (aumentado)
MAX_DOWNLOAD_THREADS = 30  # Threads dedicadas para downloads
FAZER_DOWNLOAD_ARQUIVOS = True  # True = baixar arquivos, False = só salvar info no banco
PASTA_DOWNLOADS = "downloads_licitacoes"  # Pasta raiz para downloads
DOWNLOAD_TIMEOUT = 30  # Timeout de download em segundos

# Lock para sincronizar escritas no banco
db_lock = threading.Lock()

# Pool de threads dedicado para downloads
download_executor = None  # Será inicializado depois

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
# CRIAR/ATUALIZAR ESTRUTURA DO BANCO DE DADOS
# ============================================================================

def criar_estrutura_banco():
    """Cria todas as tabelas necessárias no banco"""
    print(f"\n{'='*70}")
    print("CRIANDO/VERIFICANDO ESTRUTURA DO BANCO DE DADOS")
    print(f"{'='*70}")
    
    conn = sqlite3.connect('licitacoes.db')
    cursor = conn.cursor()
    
    # Tabela principal de licitações
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS licitacoes (
            id TEXT PRIMARY KEY,
            numero_controle_pncp TEXT,
            titulo TEXT,
            descricao TEXT,
            orgao_cnpj TEXT,
            orgao_nome TEXT,
            unidade_nome TEXT,
            uf TEXT,
            municipio_nome TEXT,
            modalidade_licitacao_nome TEXT,
            situacao_nome TEXT,
            data_publicacao_pncp TEXT,
            data_inicio_vigencia TEXT,
            data_fim_vigencia TEXT,
            valor_global REAL,
            esfera_nome TEXT,
            poder_nome TEXT,
            tipo_nome TEXT,
            cancelado INTEGER,
            tem_resultado INTEGER,
            item_url TEXT,
            url_navegador TEXT,
            dados_completos TEXT,
            atualizado_em TEXT
        )
    ''')
    
    # Tabela de itens da licitação
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS licitacao_itens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            licitacao_id TEXT,
            numero_item INTEGER,
            descricao TEXT,
            material_ou_servico TEXT,
            material_ou_servico_nome TEXT,
            valor_unitario_estimado REAL,
            valor_total REAL,
            quantidade REAL,
            unidade_medida TEXT,
            orcamento_sigiloso INTEGER,
            item_categoria_id INTEGER,
            item_categoria_nome TEXT,
            criterio_julgamento_id INTEGER,
            criterio_julgamento_nome TEXT,
            situacao_compra_item INTEGER,
            situacao_compra_item_nome TEXT,
            tipo_beneficio INTEGER,
            tipo_beneficio_nome TEXT,
            data_inclusao TEXT,
            data_atualizacao TEXT,
            tem_resultado INTEGER,
            ncm_nbs_codigo TEXT,
            ncm_nbs_descricao TEXT,
            dados_completos TEXT,
            FOREIGN KEY (licitacao_id) REFERENCES licitacoes(id)
        )
    ''')
    
    # Tabela de arquivos da licitação
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS licitacao_arquivos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            licitacao_id TEXT,
            ano_compra INTEGER,
            cnpj TEXT,
            data_publicacao_pncp TEXT,
            sequencial_compra INTEGER,
            sequencial_documento INTEGER,
            status_ativo INTEGER,
            tipo_documento_id INTEGER,
            tipo_documento_nome TEXT,
            tipo_documento_descricao TEXT,
            titulo TEXT,
            uri TEXT,
            url TEXT,
            dados_completos TEXT,
            FOREIGN KEY (licitacao_id) REFERENCES licitacoes(id)
        )
    ''')
    
    # Tabela de histórico da licitação
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS licitacao_historico (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            licitacao_id TEXT,
            justificativa TEXT,
            tipo_log_manutencao INTEGER,
            categoria_log_manutencao INTEGER,
            log_manutencao_data_inclusao TEXT,
            usuario_nome TEXT,
            compra_sequencial INTEGER,
            item_numero INTEGER,
            item_resultado_numero INTEGER,
            documento_tipo TEXT,
            documento_titulo TEXT,
            documento_sequencial INTEGER,
            tipo_log_manutencao_nome TEXT,
            categoria_log_manutencao_nome TEXT,
            compra_orgao_cnpj TEXT,
            compra_ano INTEGER,
            dados_completos TEXT,
            FOREIGN KEY (licitacao_id) REFERENCES licitacoes(id)
        )
    ''')
    
    # Criar índices
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_uf ON licitacoes(uf)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_municipio ON licitacoes(municipio_nome)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_data_pub ON licitacoes(data_publicacao_pncp)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_situacao ON licitacoes(situacao_nome)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_url_navegador ON licitacoes(url_navegador)')
    
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_itens_licitacao ON licitacao_itens(licitacao_id)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_arquivos_licitacao ON licitacao_arquivos(licitacao_id)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_historico_licitacao ON licitacao_historico(licitacao_id)')
    
    conn.commit()
    conn.close()
    
    print("✓ Estrutura do banco criada/verificada com sucesso")
    print(f"{'='*70}")

criar_estrutura_banco()

# ============================================================================
# INICIALIZAR POOL DE THREADS PARA DOWNLOADS
# ============================================================================
if FAZER_DOWNLOAD_ARQUIVOS:
    download_executor = ThreadPoolExecutor(max_workers=MAX_DOWNLOAD_THREADS, thread_name_prefix="Download")
    print(f"✓ Pool de downloads inicializado: {MAX_DOWNLOAD_THREADS} threads paralelas")

# ============================================================================
# FUNÇÕES PARA BUSCAR DADOS DETALHADOS NA API
# ============================================================================

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


def sanitizar_nome_arquivo(nome):
    """Remove caracteres inválidos de nomes de arquivo"""
    import re
    # Remove caracteres inválidos para Windows/Linux
    nome_limpo = re.sub(r'[<>:"/\\|?*]', '_', nome)
    # Limita tamanho
    if len(nome_limpo) > 200:
        nome_limpo = nome_limpo[:200]
    return nome_limpo


def fazer_download_arquivo(url, caminho_destino, tentativas=2):
    """Faz download de um arquivo com retry (otimizado)"""
    for tentativa in range(tentativas):
        try:
            # Headers para acelerar
            headers = {
                'User-Agent': 'Mozilla/5.0',
                'Accept-Encoding': 'gzip, deflate',
                'Connection': 'keep-alive'
            }
            
            response = requests.get(
                url, 
                timeout=DOWNLOAD_TIMEOUT, 
                stream=True,
                headers=headers,
                verify=True  # SSL verification
            )
            
            if response.status_code == 200:
                # Criar diretório se não existir (com lock para evitar race condition)
                dir_path = os.path.dirname(caminho_destino)
                if not os.path.exists(dir_path):
                    try:
                        os.makedirs(dir_path, exist_ok=True)
                    except FileExistsError:
                        pass  # Outro thread já criou
                
                # Salvar arquivo com buffer maior para velocidade
                with open(caminho_destino, 'wb') as f:
                    for chunk in response.iter_content(chunk_size=65536):  # 64KB chunks
                        if chunk:
                            f.write(chunk)
                
                # Verificar se arquivo foi salvo
                if os.path.exists(caminho_destino) and os.path.getsize(caminho_destino) > 0:
                    return True, os.path.getsize(caminho_destino)
                else:
                    return False, "Arquivo vazio"
                    
            else:
                if tentativa == tentativas - 1:
                    return False, f"HTTP {response.status_code}"
                time.sleep(0.5)
                
        except Exception as e:
            if tentativa == tentativas - 1:
                return False, str(e)
            time.sleep(0.5)
    
    return False, "Falha após tentativas"


def download_arquivo_async(arquivo_info):
    """Função auxiliar para download assíncrono"""
    url, caminho_destino = arquivo_info
    
    # Verificar se já existe
    if os.path.exists(caminho_destino) and os.path.getsize(caminho_destino) > 0:
        return True, 0, "já existia"
    
    sucesso, info = fazer_download_arquivo(url, caminho_destino)
    return sucesso, info if isinstance(info, int) else 0, info


def organizar_arquivos_licitacao(cnpj, ano, sequencial, arquivos, licitacao_titulo):
    """
    Organiza e prepara downloads dos arquivos de uma licitação (PARALELO)
    
    Estrutura de pastas:
    downloads_licitacoes/
    ├── CNPJ_ORGAO/
    │   ├── ANO/
    │   │   ├── SEQUENCIAL_TITULO/
    │   │   │   ├── editais/
    │   │   │   ├── anexos/
    │   │   │   ├── atas/
    │   │   │   └── outros/
    """
    if not FAZER_DOWNLOAD_ARQUIVOS or not arquivos:
        return 0, 0
    
    # Sanitizar título para usar no nome da pasta
    titulo_limpo = sanitizar_nome_arquivo(licitacao_titulo)[:50]
    
    # Pasta base da licitação
    pasta_licitacao = os.path.join(
        PASTA_DOWNLOADS,
        str(cnpj),
        str(ano),
        f"{sequencial}_{titulo_limpo}"
    )
    
    # Preparar lista de downloads
    downloads_para_fazer = []
    
    for arquivo in arquivos:
        tipo_doc = arquivo.get('tipoDocumentoNome', 'outros').lower()
        titulo_arquivo = arquivo.get('titulo', 'sem_titulo')
        url_download = arquivo.get('url')
        
        if not url_download:
            continue
        
        # Determinar subpasta por tipo
        if 'edital' in tipo_doc:
            subpasta = 'editais'
        elif 'anexo' in tipo_doc:
            subpasta = 'anexos'
        elif 'ata' in tipo_doc:
            subpasta = 'atas'
        elif 'termo' in tipo_doc or 'referência' in tipo_doc or 'referencia' in tipo_doc:
            subpasta = 'termos'
        elif 'aviso' in tipo_doc:
            subpasta = 'avisos'
        else:
            subpasta = 'outros'
        
        # Caminho completo
        pasta_destino = os.path.join(pasta_licitacao, subpasta)
        
        # Sanitizar nome do arquivo
        nome_arquivo_limpo = sanitizar_nome_arquivo(titulo_arquivo)
        
        # Se não tiver extensão, tentar extrair da URL ou usar .pdf como padrão
        if '.' not in nome_arquivo_limpo:
            if '.zip' in url_download.lower():
                nome_arquivo_limpo += '.zip'
            elif '.pdf' in url_download.lower():
                nome_arquivo_limpo += '.pdf'
            elif '.doc' in url_download.lower():
                nome_arquivo_limpo += '.doc'
            elif '.xls' in url_download.lower():
                nome_arquivo_limpo += '.xls'
            else:
                nome_arquivo_limpo += '.pdf'  # Padrão
        
        caminho_completo = os.path.join(pasta_destino, nome_arquivo_limpo)
        
        downloads_para_fazer.append((url_download, caminho_completo))
    
    # Fazer downloads em paralelo usando o executor global
    if not downloads_para_fazer:
        return 0, 0
    
    downloads_ok = 0
    downloads_falha = 0
    
    # Submeter todos os downloads em paralelo
    futures = [download_executor.submit(download_arquivo_async, info) for info in downloads_para_fazer]
    
    # Aguardar resultados (sem bloquear muito)
    for future in futures:
        try:
            sucesso, tamanho, info = future.result(timeout=DOWNLOAD_TIMEOUT + 5)
            if sucesso:
                downloads_ok += 1
            else:
                downloads_falha += 1
        except Exception:
            downloads_falha += 1
    
    return downloads_ok, downloads_falha


def buscar_historico_licitacao(cnpj, ano, sequencial):
    """Busca todo o histórico de uma licitação"""
    url = f"https://pncp.gov.br/api/pncp/v1/orgaos/{cnpj}/compras/{ano}/{sequencial}/historico"
    
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
# FUNÇÃO PARA COLETAR DADOS DE UM ESTADO (THREAD)
# ============================================================================

def coletar_estado(uf, estatistica):
    """Coleta dados de um único estado (executado em thread)"""
    if estatistica == 0:
        print(f"\n⊘ [{uf}] Sem licitações")
        return uf, []
    
    print(f"\n{'='*70}")
    print(f"[{uf}] INICIANDO COLETA ({estatistica} licitações)")
    print(f"{'='*70}")
    
    params = {
        "tipos_documento": "edital",
        "ordenacao": "-data",
        "tam_pagina": 100,
        "status": "recebendo_proposta",
        "ufs": uf,
        "pagina": 1
    }
    
    total_paginas = min((estatistica // 100) + 1, 100)
    itens_uf = []
    
    for pagina in range(1, total_paginas + 1):
        print(f"  [{uf}] Página {pagina}/{total_paginas}...", end=" ")
        params["pagina"] = pagina
        
        try:
            response = requests.get(base_url, params=params, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                itens = data.get("items", [])
                itens_uf.extend(itens)
                print(f"✓ {len(itens)} itens")
            else:
                print(f"✗ Erro {response.status_code}")
                break
                
        except Exception as e:
            print(f"✗ {e}")
            break
        
        time.sleep(0.2)
    
    # Salvar arquivo individual da UF
    if itens_uf:
        nome_arquivo_uf = f"licitacoes_{uf}.json"
        with open(nome_arquivo_uf, 'w', encoding='utf-8') as f:
            json.dump(itens_uf, f, ensure_ascii=False, indent=2)
        
        print(f"  [{uf}] ✓ Concluído: {len(itens_uf)} licitações salvas")
    else:
        print(f"  [{uf}] ⚠️  Nenhuma licitação coletada")
    
    return uf, itens_uf

# ============================================================================
# COLETA PARALELA COM THREADS
# ============================================================================

print(f"\n{'='*70}")
print(f"INICIANDO COLETA PARALELA COM {MAX_THREADS} THREADS")
print(f"{'='*70}")

todos_itens_geral = []
arquivos_gerados = []

# Filtrar apenas estados com licitações
estados_para_coletar = [(uf, estatisticas_previas[uf]) for uf in UFS if estatisticas_previas[uf] > 0]

print(f"\n✓ {len(estados_para_coletar)} estados com licitações")
print(f"✓ Processando {MAX_THREADS} estados por vez\n")

# Executar coleta em paralelo
with ThreadPoolExecutor(max_workers=MAX_THREADS) as executor:
    # Submeter tarefas
    futures = {executor.submit(coletar_estado, uf, est): uf for uf, est in estados_para_coletar}
    
    # Processar resultados conforme completam
    for future in as_completed(futures):
        uf = futures[future]
        try:
            uf_resultado, itens_uf = future.result()
            
            if itens_uf:
                arquivos_gerados.append(f"licitacoes_{uf_resultado}.json")
                todos_itens_geral.extend(itens_uf)
                
        except Exception as e:
            print(f"\n✗ [{uf}] Erro na thread: {e}")

# Salvar arquivo consolidado
data_hora = datetime.now().strftime("%Y%m%d_%H%M%S")
nome_arquivo_geral = f"licitacoes_completo_{data_hora}.json"

with open(nome_arquivo_geral, 'w', encoding='utf-8') as f:
    json.dump(todos_itens_geral, f, ensure_ascii=False, indent=2)

# Resumo
print(f"\n{'='*70}")
print("COLETA BÁSICA FINALIZADA!")
print(f"{'='*70}")
print(f"Arquivo consolidado: {nome_arquivo_geral}")
print(f"Total de licitações coletadas: {len(todos_itens_geral)}")
print(f"{'='*70}")

# Deletar arquivos individuais
print(f"\nDeletando arquivos individuais por UF...")
for arq in arquivos_gerados:
    try:
        os.remove(arq)
        print(f"  ✓ Deletado: {arq}")
    except Exception as e:
        print(f"  ✗ Erro ao deletar {arq}: {e}")

# ============================================================================
# POPULAR BANCO COM DADOS COMPLETOS
# ============================================================================

def popular_licitacao_no_banco(licitacao, cursor, agora):
    """Popula uma licitação individual no banco (thread-safe)"""
    licitacao_id = licitacao.get('id')
    
    # Extrair dados da URL
    item_url = licitacao.get('item_url', '')
    url_navegador = f"https://pncp.gov.br/app/editais{item_url.replace('/compras', '')}" if item_url else None
    
    # Dados para buscar detalhes
    cnpj = licitacao.get('orgao_cnpj')
    ano = licitacao.get('ano')
    sequencial = licitacao.get('numero_sequencial')
    
    # 1. Inserir licitação principal
    with db_lock:
        cursor.execute('''
            INSERT OR REPLACE INTO licitacoes (
                id, numero_controle_pncp, titulo, descricao,
                orgao_cnpj, orgao_nome, unidade_nome,
                uf, municipio_nome, modalidade_licitacao_nome,
                situacao_nome, data_publicacao_pncp,
                data_inicio_vigencia, data_fim_vigencia,
                valor_global, esfera_nome, poder_nome,
                tipo_nome, cancelado, tem_resultado,
                item_url, url_navegador, dados_completos, atualizado_em
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ''', (
            licitacao_id,
            licitacao.get('numero_controle_pncp'),
            licitacao.get('title'),
            licitacao.get('description'),
            cnpj,
            licitacao.get('orgao_nome'),
            licitacao.get('unidade_nome'),
            licitacao.get('uf'),
            licitacao.get('municipio_nome'),
            licitacao.get('modalidade_licitacao_nome'),
            licitacao.get('situacao_nome'),
            licitacao.get('data_publicacao_pncp'),
            licitacao.get('data_inicio_vigencia'),
            licitacao.get('data_fim_vigencia'),
            licitacao.get('valor_global'),
            licitacao.get('esfera_nome'),
            licitacao.get('poder_nome'),
            licitacao.get('tipo_nome'),
            1 if licitacao.get('cancelado') else 0,
            1 if licitacao.get('tem_resultado') else 0,
            item_url,
            url_navegador,
            json.dumps(licitacao, ensure_ascii=False),
            agora
        ))
    
    contador_itens = 0
    contador_arquivos = 0
    contador_historico = 0
    
    # 2. Buscar e inserir ITENS
    if cnpj and ano and sequencial:
        itens = buscar_itens_licitacao(cnpj, ano, sequencial)
        
        if itens:
            with db_lock:
                for item in itens:
                    cursor.execute('''
                        INSERT INTO licitacao_itens (
                            licitacao_id, numero_item, descricao,
                            material_ou_servico, material_ou_servico_nome,
                            valor_unitario_estimado, valor_total,
                            quantidade, unidade_medida, orcamento_sigiloso,
                            item_categoria_id, item_categoria_nome,
                            criterio_julgamento_id, criterio_julgamento_nome,
                            situacao_compra_item, situacao_compra_item_nome,
                            tipo_beneficio, tipo_beneficio_nome,
                            data_inclusao, data_atualizacao, tem_resultado,
                            ncm_nbs_codigo, ncm_nbs_descricao, dados_completos
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ''', (
                        licitacao_id,
                        item.get('numeroItem'),
                        item.get('descricao'),
                        item.get('materialOuServico'),
                        item.get('materialOuServicoNome'),
                        item.get('valorUnitarioEstimado'),
                        item.get('valorTotal'),
                        item.get('quantidade'),
                        item.get('unidadeMedida'),
                        1 if item.get('orcamentoSigiloso') else 0,
                        item.get('itemCategoriaId'),
                        item.get('itemCategoriaNome'),
                        item.get('criterioJulgamentoId'),
                        item.get('criterioJulgamentoNome'),
                        item.get('situacaoCompraItem'),
                        item.get('situacaoCompraItemNome'),
                        item.get('tipoBeneficio'),
                        item.get('tipoBeneficioNome'),
                        item.get('dataInclusao'),
                        item.get('dataAtualizacao'),
                        1 if item.get('temResultado') else 0,
                        item.get('ncmNbsCodigo'),
                        item.get('ncmNbsDescricao'),
                        json.dumps(item, ensure_ascii=False)
                    ))
                    contador_itens += 1
        
        # 3. Buscar e inserir ARQUIVOS
        arquivos = buscar_arquivos_licitacao(cnpj, ano, sequencial)
        
        if arquivos:
            # Fazer download dos arquivos em paralelo (se habilitado)
            downloads_ok = 0
            downloads_falha = 0
            if FAZER_DOWNLOAD_ARQUIVOS and download_executor:
                titulo_lic = licitacao.get('title', 'sem_titulo')
                downloads_ok, downloads_falha = organizar_arquivos_licitacao(
                    cnpj, ano, sequencial, arquivos, titulo_lic
                )
            
            with db_lock:
                for arquivo in arquivos:
                    cursor.execute('''
                        INSERT INTO licitacao_arquivos (
                            licitacao_id, ano_compra, cnpj,
                            data_publicacao_pncp, sequencial_compra,
                            sequencial_documento, status_ativo,
                            tipo_documento_id, tipo_documento_nome,
                            tipo_documento_descricao, titulo, uri, url,
                            dados_completos
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ''', (
                        licitacao_id,
                        arquivo.get('anoCompra'),
                        arquivo.get('cnpj'),
                        arquivo.get('dataPublicacaoPncp'),
                        arquivo.get('sequencialCompra'),
                        arquivo.get('sequencialDocumento'),
                        1 if arquivo.get('statusAtivo') else 0,
                        arquivo.get('tipoDocumentoId'),
                        arquivo.get('tipoDocumentoNome'),
                        arquivo.get('tipoDocumentoDescricao'),
                        arquivo.get('titulo'),
                        arquivo.get('uri'),
                        arquivo.get('url'),
                        json.dumps(arquivo, ensure_ascii=False)
                    ))
                    contador_arquivos += 1
        
        # 4. Buscar e inserir HISTÓRICO
        historico = buscar_historico_licitacao(cnpj, ano, sequencial)
        
        if historico:
            with db_lock:
                for hist in historico:
                    cursor.execute('''
                        INSERT INTO licitacao_historico (
                            licitacao_id, justificativa, tipo_log_manutencao,
                            categoria_log_manutencao, log_manutencao_data_inclusao,
                            usuario_nome, compra_sequencial, item_numero,
                            item_resultado_numero, documento_tipo, documento_titulo,
                            documento_sequencial, tipo_log_manutencao_nome,
                            categoria_log_manutencao_nome, compra_orgao_cnpj,
                            compra_ano, dados_completos
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ''', (
                        licitacao_id,
                        hist.get('justificativa'),
                        hist.get('tipoLogManutencao'),
                        hist.get('categoriaLogManutencao'),
                        hist.get('logManutencaoDataInclusao'),
                        hist.get('usuarioNome'),
                        hist.get('compraSequencial'),
                        hist.get('itemNumero'),
                        hist.get('itemResultadoNumero'),
                        hist.get('documentoTipo'),
                        hist.get('documentoTitulo'),
                        hist.get('documentoSequencial'),
                        hist.get('tipoLogManutencaoNome'),
                        hist.get('categoriaLogManutencaoNome'),
                        hist.get('compraOrgaoCnpj'),
                        hist.get('compraAno'),
                        json.dumps(hist, ensure_ascii=False)
                    ))
                    contador_historico += 1
    
    return contador_itens, contador_arquivos, contador_historico

def popular_banco_completo(arquivo_json):
    """Popula o banco com licitações e seus dados detalhados"""
    print(f"\n{'='*70}")
    print("POPULANDO BANCO DE DADOS COM INFORMAÇÕES COMPLETAS")
    print(f"{'='*70}")
    
    conn = sqlite3.connect('licitacoes.db', check_same_thread=False)
    cursor = conn.cursor()
    
    # Ler o JSON
    print(f"Lendo arquivo: {arquivo_json}")
    with open(arquivo_json, 'r', encoding='utf-8') as f:
        dados = json.load(f)
    
    print(f"Total de licitações a processar: {len(dados)}")
    
    contador_licitacoes = 0
    contador_itens = 0
    contador_arquivos = 0
    contador_historico = 0
    agora = datetime.now().isoformat()
    
    print(f"\n✓ Processando {len(dados)} licitações com {MAX_THREADS} threads...\n")
    
    # Processar licitações em paralelo
    with ThreadPoolExecutor(max_workers=MAX_THREADS) as executor:
        futures = []
        
        for idx, licitacao in enumerate(dados, 1):
            print(f"[{idx}/{len(dados)}] Processando: {licitacao.get('title', 'Sem título')[:50]}...")
            future = executor.submit(popular_licitacao_no_banco, licitacao, cursor, agora)
            futures.append((idx, future))
            
            # Commit periódico
            if idx % 50 == 0:
                # Aguardar conclusão das threads atuais antes do commit
                for i, f in futures[-50:]:
                    try:
                        itens, arquivos, historico = f.result()
                        contador_itens += itens
                        contador_arquivos += arquivos
                        contador_historico += historico
                        contador_licitacoes += 1
                    except Exception as e:
                        print(f"  ✗ Erro ao processar licitação: {e}")
                
                conn.commit()
                print(f"\n  💾 Checkpoint: {idx}/{len(dados)} licitações processadas")
                print(f"      Itens: {contador_itens} | Arquivos: {contador_arquivos} | Histórico: {contador_historico}\n")
        
        # Processar resultados finais
        print("\n⏳ Aguardando conclusão das últimas threads...")
        for idx, future in futures:
            try:
                itens, arquivos, historico = future.result()
                contador_itens += itens
                contador_arquivos += arquivos
                contador_historico += historico
                if idx % 50 != 0:  # Já contamos os múltiplos de 50
                    contador_licitacoes += 1
            except Exception as e:
                print(f"  ✗ Erro ao processar licitação {idx}: {e}")
    
    # Commit final
    conn.commit()
    
    # Estatísticas finais
    cursor.execute('SELECT COUNT(*) FROM licitacoes')
    total_licitacoes = cursor.fetchone()[0]
    
    cursor.execute('SELECT COUNT(*) FROM licitacao_itens')
    total_itens = cursor.fetchone()[0]
    
    cursor.execute('SELECT COUNT(*) FROM licitacao_arquivos')
    total_arquivos = cursor.fetchone()[0]
    
    cursor.execute('SELECT COUNT(*) FROM licitacao_historico')
    total_historico = cursor.fetchone()[0]
    
    conn.close()
    
    print(f"\n{'='*70}")
    print(f"✓ BANCO POPULADO COM SUCESSO!")
    print(f"{'='*70}")
    print(f"Licitações processadas: {contador_licitacoes}")
    print(f"Itens inseridos: {contador_itens}")
    print(f"Arquivos inseridos: {contador_arquivos}")
    print(f"Registros de histórico: {contador_historico}")
    print(f"\nTotais no banco:")
    print(f"  - Licitações: {total_licitacoes}")
    print(f"  - Itens: {total_itens}")
    print(f"  - Arquivos: {total_arquivos}")
    print(f"  - Histórico: {total_historico}")
    
    if FAZER_DOWNLOAD_ARQUIVOS:
        print(f"\n📁 DOWNLOADS:")
        print(f"  - Pasta: {PASTA_DOWNLOADS}")
        if os.path.exists(PASTA_DOWNLOADS):
            # Calcular tamanho total
            tamanho_total = 0
            num_arquivos = 0
            for root, dirs, files in os.walk(PASTA_DOWNLOADS):
                for file in files:
                    num_arquivos += 1
                    tamanho_total += os.path.getsize(os.path.join(root, file))
            
            tamanho_mb = tamanho_total / (1024 * 1024)
            print(f"  - Arquivos baixados: {num_arquivos}")
            print(f"  - Tamanho total: {tamanho_mb:.2f} MB")
    
    print(f"{'='*70}")

# Executar população do banco
popular_banco_completo(nome_arquivo_geral)

# ============================================================================
# TEMPO TOTAL DE EXECUÇÃO
# ============================================================================
# Fechar pool de downloads
if FAZER_DOWNLOAD_ARQUIVOS and download_executor:
    print("\n⏳ Finalizando downloads pendentes...")
    download_executor.shutdown(wait=True)
    print("✓ Pool de downloads finalizado")

tempo_fim = time.time()
tempo_total = tempo_fim - tempo_inicio

horas = int(tempo_total // 3600)
minutos = int((tempo_total % 3600) // 60)
segundos = int(tempo_total % 60)

print(f"\n{'='*70}")
print(f"⏱️  TEMPO TOTAL DE EXECUÇÃO")
print(f"{'='*70}")
if horas > 0:
    print(f"Tempo: {horas}h {minutos}min {segundos}s ({tempo_total:.2f} segundos)")
elif minutos > 0:
    print(f"Tempo: {minutos}min {segundos}s ({tempo_total:.2f} segundos)")
else:
    print(f"Tempo: {segundos}s ({tempo_total:.2f} segundos)")
print(f"{'='*70}")
