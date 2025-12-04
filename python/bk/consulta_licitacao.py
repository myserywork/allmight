import requests
import json
from datetime import datetime
import time
import os
import sqlite3
import glob

# ============================================================================
# INICIAR CONTAGEM DE TEMPO
# ============================================================================
tempo_inicio = time.time()

# Verificar se já existe coleta de hoje
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

# URL base da API
base_url = "https://pncp.gov.br/api/search/"

# Lista de todas as UFs do Brasil
UFS = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 
       'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 
       'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']

print("="*70)
print("VERIFICANDO QUANTIDADE DE LICITAÇÕES POR UF")
print("="*70)

# Primeiro: verificar quantas licitações tem em cada UF
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


# Agora buscar todos os dados
print(f"\n{'='*70}")
print("INICIANDO COLETA COMPLETA POR UF")
print(f"{'='*70}")

todos_itens_geral = []
arquivos_gerados = []

for uf in UFS:
    if estatisticas_previas[uf] == 0:
        print(f"\n⊘ Pulando {uf} (sem licitações)")
        continue
    
    print(f"\n{'='*70}")
    print(f"COLETANDO: {uf} ({estatisticas_previas[uf]} licitações)")
    print(f"{'='*70}")
    
    params = {
        "tipos_documento": "edital",
        "ordenacao": "-data",
        "tam_pagina": 100,
        "status": "recebendo_proposta",
        "ufs": uf,
        "pagina": 1
    }
    
    total_paginas = min((estatisticas_previas[uf] // 100) + 1, 100)
    itens_uf = []
    
    for pagina in range(1, total_paginas + 1):
        print(f"  Página {pagina}/{total_paginas}...", end=" ")
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
        
        arquivos_gerados.append(nome_arquivo_uf)
        todos_itens_geral.extend(itens_uf)
        print(f"  ✓ Arquivo salvo: {nome_arquivo_uf} ({len(itens_uf)} itens)")

# Salvar arquivo consolidado
data_hora = datetime.now().strftime("%Y%m%d_%H%M%S")
nome_arquivo_geral = f"licitacoes_completo_{data_hora}.json"

with open(nome_arquivo_geral, 'w', encoding='utf-8') as f:
    json.dump(todos_itens_geral, f, ensure_ascii=False, indent=2)

# Resumo final
print(f"\n{'='*70}")
print("COLETA FINALIZADA!")
print(f"{'='*70}")
print(f"\nArquivos individuais gerados ({len(arquivos_gerados)}):")
for arq in arquivos_gerados:
    print(f"  - {arq}")
print(f"\nArquivo consolidado: {nome_arquivo_geral}")
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

print(f"\n✓ Arquivo final disponível: {nome_arquivo_geral}")
print(f"{'='*70}")

# ============================================================================
# ATUALIZAR BANCO DE DADOS SQLITE
# ============================================================================

def atualizar_banco(arquivo_json):
    """
    Cria ou atualiza o banco de dados SQLite com os dados do JSON.
    - Se o banco não existir, cria a estrutura
    - Se já existir, faz UPSERT (insert ou update)
    """
    print(f"\n{'='*70}")
    print("ATUALIZANDO BANCO DE DADOS")
    print(f"{'='*70}")
    
    # Conectar ao banco (cria se não existir)
    conn = sqlite3.connect('licitacoes.db')
    cursor = conn.cursor()
    
    # Criar tabela se não existir
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
    
    # Criar índices para consultas rápidas
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_uf ON licitacoes(uf)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_municipio ON licitacoes(municipio_nome)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_data_pub ON licitacoes(data_publicacao_pncp)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_situacao ON licitacoes(situacao_nome)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_url_navegador ON licitacoes(url_navegador)')
    
    print("✓ Estrutura do banco verificada/criada")
    
    # Ler o JSON
    print(f"Lendo arquivo: {arquivo_json}")
    with open(arquivo_json, 'r', encoding='utf-8') as f:
        dados = json.load(f)
    
    print(f"Total de registros a processar: {len(dados)}")
    
    # Inserir/atualizar dados
    contador = 0
    agora = datetime.now().isoformat()
    
    for item in dados:
        # INSERT OR REPLACE (UPSERT)
        # Construir URL para navegador
        item_url_original = item.get('item_url', '')
        url_navegador = f"https://pncp.gov.br/app/editais{item_url_original.replace('/compras', '')}" if item_url_original else None
        
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
            item.get('id'),
            item.get('numero_controle_pncp'),
            item.get('title'),
            item.get('description'),
            item.get('orgao_cnpj'),
            item.get('orgao_nome'),
            item.get('unidade_nome'),
            item.get('uf'),
            item.get('municipio_nome'),
            item.get('modalidade_licitacao_nome'),
            item.get('situacao_nome'),
            item.get('data_publicacao_pncp'),
            item.get('data_inicio_vigencia'),
            item.get('data_fim_vigencia'),
            item.get('valor_global'),
            item.get('esfera_nome'),
            item.get('poder_nome'),
            item.get('tipo_nome'),
            1 if item.get('cancelado') else 0,
            1 if item.get('tem_resultado') else 0,
            item.get('item_url'),
            url_navegador,
            json.dumps(item, ensure_ascii=False),  # JSON completo
            agora
        ))
        
        contador += 1
        if contador % 1000 == 0:
            print(f"  Processados: {contador}/{len(dados)}")
    
    # Commit e fechar
    conn.commit()
    
    # Estatísticas
    cursor.execute('SELECT COUNT(*) FROM licitacoes')
    total = cursor.fetchone()[0]
    
    conn.close()
    
    print(f"\n{'='*70}")
    print(f"✓ Banco atualizado com sucesso!")
    print(f"✓ Total de registros processados: {contador}")
    print(f"✓ Total no banco: {total}")
    print(f"✓ Arquivo do banco: licitacoes.db")
    print(f"{'='*70}")

# Chamar função para atualizar banco
atualizar_banco(nome_arquivo_geral)

# ============================================================================
# TEMPO TOTAL DE EXECUÇÃO
# ============================================================================
tempo_fim = time.time()
tempo_total = tempo_fim - tempo_inicio

# Converter para formato legível
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
