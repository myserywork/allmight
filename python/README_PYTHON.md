# 🐍 Script Python - Coleta de Licitações PNCP

## 📋 Visão Geral

Script automatizado para coleta de licitações do Portal Nacional de Contratações Públicas (PNCP) e armazenamento no banco de dados MySQL do sistema Allmight.

### Características Principais

- ✅ **Coleta Paralela**: 20 threads simultâneas para buscar estados em paralelo
- ✅ **Download Assíncrono**: 30 threads dedicadas para baixar arquivos (editais, anexos, etc.)
- ✅ **Organização Automática**: Arquivos categorizados em pastas por CNPJ/Ano/Sequencial
- ✅ **Backup JSON**: Salva cópia de segurança de todas as licitações coletadas
- ✅ **Atualização Inteligente**: Detecta licitações já existentes e atualiza apenas dados novos
- ✅ **Encoding UTF-8**: Suporte completo para caracteres especiais (Windows-safe)

---

## 🚀 Configuração Inicial

### 1. Criar Ambiente Virtual

```powershell
# Navegue até a pasta python
cd c:\xampp\htdocs\allmight\python

# Crie o ambiente virtual
python -m venv venv
```

### 2. Ativar Ambiente Virtual

```powershell
# Ative o ambiente (PowerShell)
.\venv\Scripts\Activate.ps1

# Se der erro de ExecutionPolicy, execute antes:
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**Indicador de sucesso**: O terminal mostrará `(venv)` antes do prompt.

### 3. Instalar Dependências

```powershell
# Com o venv ativado, instale os pacotes
pip install -r requirements.txt
```

**Pacotes instalados**:
- `requests==2.31.0` - Para fazer chamadas à API do PNCP
- `mysql-connector-python==8.2.0` - Para conectar ao banco MySQL

---

## ⚙️ Configuração do Banco de Dados

### Verificar Configurações

No arquivo `consulta_licitacao_mysql.py`, linhas 24-31:

```python
MYSQL_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',  # senha em branco (XAMPP padrão)
    'database': 'allmight',
    'charset': 'utf8mb4',
    'collation': 'utf8mb4_unicode_ci',
    'autocommit': False
}
```

### Verificação Automática

O script verifica automaticamente:
- ✅ Se o MySQL está rodando
- ✅ Se o banco `allmight` existe
- ✅ Se as tabelas necessárias existem
- ✅ Se a fonte "PNCP" está cadastrada

**Se houver erro**: Abra `http://localhost/phpmyadmin` e execute o arquivo `sql/allmight.sql`.

---

## 🎯 Executando o Script

### Comando Básico

```powershell
# Com o venv ativado
python consulta_licitacao_mysql.py
```

### Escolha de Abrangência

Ao executar, você verá:

```
SELEÇÃO DE ESTADOS PARA COLETA
======================================================================
Escolha a abrangência da coleta:
  1 - Apenas DF (RÁPIDO - para testes)
  2 - Todos os estados (COMPLETO - ~20-30 minutos)
======================================================================

Digite sua escolha (1 ou 2) [padrão: 1]:
```

#### Opção 1: Apenas DF (Recomendado para Testes)
- ⏱️ **Tempo**: 2-5 minutos
- 📊 **Volume**: ~500-1000 licitações
- 💡 **Ideal para**: Primeiro teste, verificar funcionamento

#### Opção 2: Todos os Estados (Nacional)
- ⏱️ **Tempo**: 20-30 minutos
- 📊 **Volume**: ~10.000-50.000 licitações (depende do período)
- 💡 **Ideal para**: Coleta completa, produção

---

## 📊 O Que o Script Faz

### 1. Verificação Prévia

Antes de iniciar, verifica quantas licitações cada estado tem:

```
VERIFICANDO QUANTIDADE DE LICITAÇÕES POR UF
======================================================================
✓ AC:    245 licitações
✓ AL:    512 licitações
✓ DF:    823 licitações
⚠️  ATENÇÃO SP: 15.234 licitações  (pode não pegar tudo)
...
TOTAL GERAL: 45.678 licitações
======================================================================
```

### 2. Coleta Paralela

Coleta dados de múltiplos estados simultaneamente:

```
🔄 [DF] Iniciando coleta...
  [DF] Total: 823 licitações em ~9 páginas
  [DF] Página 1/9 - 100 licitações...................................✓✓✓✓
  [DF] Página 2/9 - 100 licitações...................................✓✓✓✓
```

**Indicadores durante coleta**:
- `.` = Buscando dados detalhados
- `✓` = Salvo com sucesso no banco
- `✗` = Erro ao salvar
- `X` = Dados incompletos (falta CNPJ/ano/sequencial)
- `!` = Exceção durante processamento

### 3. Para Cada Licitação, Coleta

#### Dados Principais
- 📝 Informações básicas (título, objeto, valores)
- 🏢 Dados do órgão (CNPJ, nome, UF, município)
- 📅 Datas (publicação, vigência, abertura)
- 🏷️ Modalidade e situação

#### Dados Relacionados
- 🛒 **Itens**: Produtos/serviços licitados (NCM, quantidade, valores)
- 📎 **Arquivos**: Editais, anexos, atas, termos
- 📜 **Histórico**: Log de alterações e justificativas

### 4. Organização de Arquivos

Downloads são organizados automaticamente:

```
downloads_licitacoes/
├── 00394494000158/              # CNPJ do órgão
│   └── 2024/                    # Ano da licitação
│       └── 1234_Aquisicao_Computadores/  # Sequencial_Título
│           ├── editais/
│           │   └── Edital_Completo.pdf
│           ├── anexos/
│           │   ├── Anexo_I_TRF.pdf
│           │   └── Anexo_II_Planilha.xlsx
│           └── atas/
│               └── Ata_Abertura.pdf
```

### 5. Salvamento no Banco

#### Tabela `licitacoes`
```sql
INSERT INTO licitacoes (
    id, fonte_id, id_externo, numero_controle_pncp,
    titulo, objeto, orgao_cnpj, orgao_nome,
    uf, municipio, modalidade, situacao,
    valor_estimado, link_portal, ...
)
```

#### Tabela `licitacao_itens`
```sql
INSERT INTO licitacao_itens (
    licitacao_id, numero_item, descricao,
    quantidade, unidade_medida,
    valor_unitario_estimado, valor_total_estimado,
    ncm_nbs_codigo, ...
)
```

#### Tabela `licitacao_arquivos`
```sql
INSERT INTO licitacao_arquivos (
    licitacao_id, titulo, tipo_documento,
    url_download, data_publicacao, ...
)
```

#### Tabela `licitacao_historico`
```sql
INSERT INTO licitacao_historico (
    licitacao_id, tipo_log, descricao,
    usuario_nome, data_inclusao, ...
)
```

### 6. Atualização Inteligente

Se a licitação já existe (mesmo `id_externo`), atualiza apenas:
- Título
- Situação
- Valor estimado
- JSON completo
- Data de atualização

---

## 📈 Relatório Final

Ao concluir, você verá:

```
======================================================================
COLETA CONCLUÍDA!
======================================================================
Tempo total: 4min 32s
Total coletado: 823 licitações

Resumo por Estado:
UF   Previsto  Coletado     Status
----------------------------------------------------------------------
DF        823       823          ✓
======================================================================

✓ Backup JSON salvo: licitacoes_completo_20241209_143022.json (12.45 MB)

⏳ Aguardando downloads em background finalizarem...
✓ Todos os downloads concluídos!

======================================================================
🎉 PROCESSO COMPLETO!
======================================================================
📊 Banco MySQL: 823 licitações
💾 Backup JSON: licitacoes_completo_20241209_143022.json
📁 Downloads: downloads_licitacoes/
======================================================================
```

---

## 🔧 Configurações Avançadas

### Ajustar Performance

No arquivo `consulta_licitacao_mysql.py`, linhas 37-42:

```python
# Tempo entre requests (evitar bloqueio por rate limit)
DELAY_ENTRE_REQUESTS = 0.1  # segundos

# Threads paralelas por estado
MAX_THREADS = 20  # Padrão: 20 (recomendado)

# Threads para downloads de arquivos
MAX_DOWNLOAD_THREADS = 30  # Padrão: 30

# Timeout de download
DOWNLOAD_TIMEOUT = 30  # segundos
```

**Recomendações**:
- 🐌 **Conexão lenta**: Reduza `MAX_THREADS` para 10
- 🚀 **Conexão rápida**: Aumente para 30-40
- ⚠️ **Rate Limit**: Aumente `DELAY_ENTRE_REQUESTS` para 0.2-0.5

### Desabilitar Downloads

```python
# Linha 41
FAZER_DOWNLOAD_ARQUIVOS = False  # Apenas salva info no banco
```

**Quando usar**:
- Primeira coleta (só testar dados)
- Economizar espaço em disco
- Focar em velocidade

---

## ❓ Solução de Problemas

### Erro: "ModuleNotFoundError: No module named 'requests'"

**Causa**: Dependências não instaladas ou venv não ativado.

**Solução**:
```powershell
.\venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

### Erro: "Can't connect to MySQL server"

**Causa**: XAMPP não está rodando ou MySQL parado.

**Solução**:
1. Abra o XAMPP Control Panel
2. Inicie os serviços "Apache" e "MySQL"
3. Aguarde status "Running" (verde)

### Erro: "Table 'licitacoes' doesn't exist"

**Causa**: Banco não foi criado ou schema não foi executado.

**Solução**:
1. Acesse `http://localhost/phpmyadmin`
2. Crie o banco `allmight` (se não existir)
3. Selecione o banco
4. Vá em "Importar" > Escolha `sql/allmight.sql` > Execute

### Erro: "Fonte PNCP não encontrada"

**Causa**: Tabela `fontes_licitacao` vazia.

**Solução**: Execute novamente o schema SQL completo.

### Aviso: "⚠️ ATENÇÃO SP: 15.234 licitações (pode não pegar tudo)"

**Causa**: API do PNCP limita resultados em 10.000 por busca.

**Solução**: Normal. Estados grandes podem ter limitação. O script pega o máximo possível (100 páginas × 100 itens = 10.000).

---

## 📝 Arquivos Gerados

### 1. Backup JSON

**Nome**: `licitacoes_completo_YYYYMMDD_HHMMSS.json`

**Exemplo**:
```json
[
  {
    "id": "uuid-123-456",
    "titulo": "Aquisição de computadores",
    "orgao_nome": "Secretaria de Educação",
    "valor_global": 150000.00,
    "itens": [...],
    "arquivos": [...],
    "historico": [...]
  }
]
```

**Uso**:
- Recuperar dados se houver problema no banco
- Análise offline
- Importar em outros sistemas

### 2. Downloads Organizados

**Estrutura**:
```
downloads_licitacoes/
├── {CNPJ}/
│   └── {ANO}/
│       └── {SEQUENCIAL}_{TITULO}/
│           ├── editais/
│           ├── anexos/
│           ├── atas/
│           ├── termos/
│           ├── avisos/
│           └── outros/
```

---

## 🔄 Atualizações Periódicas

### Coleta Diária Recomendada

```powershell
# Ativar venv
.\venv\Scripts\Activate.ps1

# Executar coleta completa
python consulta_licitacao_mysql.py
# Escolha opção 2 (todos os estados)
```

### Automatizar com Agendador (Windows)

1. Abra "Agendador de Tarefas"
2. Criar Tarefa Básica
3. Acionar: Diariamente às 06:00
4. Ação: Iniciar Programa
   - Programa: `powershell.exe`
   - Argumentos: `-File C:\xampp\htdocs\allmight\python\executar_coleta.ps1`

**Arquivo `executar_coleta.ps1`**:
```powershell
cd C:\xampp\htdocs\allmight\python
.\venv\Scripts\Activate.ps1
python consulta_licitacao_mysql.py
```

---

## 📊 Estatísticas de Uso

### Tempo Médio de Execução

| Modo | Estados | Licitações | Tempo Médio |
|------|---------|-----------|-------------|
| Teste (DF) | 1 | 500-1.000 | 2-5 min |
| Nacional | 27 | 10.000-50.000 | 20-30 min |

### Consumo de Recursos

- **CPU**: 20-40% (depende do número de threads)
- **RAM**: 200-500 MB
- **Rede**: 10-50 MB/min (download de PDFs)
- **Disco**: 50-500 MB por coleta (com downloads)

---

## 🔐 Segurança e Boas Práticas

### ✅ Já Configurado no .gitignore

```
venv/
.venv/
*.pyc
__pycache__/
downloads_licitacoes/
licitacoes_completo_*.json
```

### ⚠️ Nunca Commitar

- ❌ Pasta `venv/`
- ❌ Arquivos JSON de backup
- ❌ Pasta `downloads_licitacoes/`
- ❌ Senhas de banco de dados (use `.env` se mudar)

---

## 🆘 Suporte

### Logs e Debug

O script imprime logs detalhados no terminal:
- Estados sendo processados
- Progresso por página
- Erros de conexão ou salvamento
- Tempo total de execução

### Contato

Para problemas ou dúvidas:
1. Verifique este README
2. Consulte os logs de erro no terminal
3. Verifique o banco de dados via phpMyAdmin

---

## 📚 Referências

- **API PNCP**: https://pncp.gov.br/api/
- **Documentação PNCP**: https://pncp.gov.br/
- **MySQL Connector**: https://dev.mysql.com/doc/connector-python/en/
- **Requests**: https://requests.readthedocs.io/

---

## 📄 Licença

Este script faz parte do sistema Allmight e está sujeito à mesma licença do projeto principal.

---

**Última atualização**: 09/12/2024
