# 🚀 MIGRAÇÃO PARA MYSQL - SISTEMA ALLMIGHT

## 📋 O QUE FOI FEITO

### ✅ Arquivos Criados

1. **`schema_allmight_mysql.sql`** - Schema completo do banco MySQL
   - 20+ tabelas integradas
   - 3 views úteis
   - Índices otimizados
   - Configurações padrão

2. **`consulta_licitacao_mysql.py`** - Script de coleta adaptado para MySQL
   - Usa MySQL ao invés de SQLite
   - Mantém todas as funcionalidades (threads, downloads, etc)
   - Compatível com novo schema

## 🔧 CONFIGURAÇÃO INICIAL

### 1. Preparar o MySQL (XAMPP)

1. Inicie o **XAMPP Control Panel**
2. Start **MySQL** e **Apache**
3. Acesse o **phpMyAdmin**: http://localhost/phpmyadmin

### 2. Criar o Banco de Dados

No phpMyAdmin:

```sql
CREATE DATABASE allmight 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### 3. Executar o Schema

1. Clique no banco **allmight**
2. Vá em **SQL** (aba superior)
3. Copie TODO o conteúdo de `schema_allmight_mysql.sql`
4. Cole e clique em **Executar**
5. Aguarde a criação de todas as tabelas

**OU** importe o arquivo:
- Clique em **Importar**
- Escolha `schema_allmight_mysql.sql`
- Clique em **Executar**

### 4. Verificar Instalação

Execute no SQL do phpMyAdmin:

```sql
-- Verificar tabelas criadas
SHOW TABLES;

-- Deve mostrar 20+ tabelas:
-- empresas
-- perfis_empresa
-- documentos_empresa
-- projetos_empresa
-- fontes_licitacao
-- licitacoes
-- licitacao_itens
-- licitacao_arquivos
-- licitacao_historico
-- matches
-- analises_comerciais
-- propostas
-- proposta_itens
-- usuarios
-- cron_logs
-- notificacoes
-- configuracoes

-- Verificar fonte PNCP
SELECT * FROM fontes_licitacao;

-- Deve retornar 1 registro: PNCP
```

## 🎯 COMO USAR O NOVO SCRIPT

### Executar Coleta

```bash
python consulta_licitacao_mysql.py
```

### O que o script faz:

1. ✅ **Verifica conexão** com MySQL
2. ✅ **Verifica se o banco existe** e tem as tabelas
3. ✅ **Busca ID da fonte PNCP** no banco
4. ✅ **Coleta licitações** do PNCP
5. ✅ **Salva no MySQL** (não mais no SQLite!)
6. ✅ **Baixa arquivos** em paralelo
7. ✅ **Gera backup JSON**

### Opções de Coleta

Quando executar, você verá:

```
SELEÇÃO DE ESTADOS PARA COLETA
====================================
1 - Apenas DF (RÁPIDO - para testes)
2 - Todos os estados (COMPLETO)
====================================
```

- **Opção 1**: Testa com ~2.000 licitações (2-5 minutos)
- **Opção 2**: Coleta ~37.000 licitações (20-30 minutos)

## 📊 DIFERENÇAS DO SQLITE PARA MYSQL

### SQLite (Antigo)
```python
import sqlite3
conn = sqlite3.connect('licitacoes.db')
```

### MySQL (Novo)
```python
import mysql.connector
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='allmight'
)
```

### Principais Mudanças

| Aspecto | SQLite | MySQL |
|---------|--------|-------|
| **Arquivo** | `licitacoes.db` | Banco `allmight` no servidor |
| **IDs** | `INTEGER PRIMARY KEY` | `CHAR(36)` (UUID) |
| **JSON** | `TEXT` | `JSON` (tipo nativo) |
| **Booleans** | `INTEGER (0/1)` | `BOOLEAN` |
| **Timestamps** | `TEXT` | `TIMESTAMP` |
| **Foreign Keys** | Suporte básico | Suporte completo com CASCADE |

## 🔍 CONSULTAS ÚTEIS

### Ver licitações coletadas

```sql
SELECT 
    COUNT(*) as total,
    uf,
    COUNT(DISTINCT orgao_cnpj) as total_orgaos
FROM licitacoes
WHERE ativo = TRUE
GROUP BY uf
ORDER BY total DESC;
```

### Ver licitações abertas

```sql
SELECT * FROM v_licitacoes_abertas
ORDER BY dias_restantes ASC
LIMIT 20;
```

### Ver estatísticas por UF

```sql
SELECT 
    uf,
    COUNT(*) as total,
    SUM(valor_estimado) as valor_total,
    AVG(valor_estimado) as valor_medio,
    COUNT(DISTINCT modalidade) as tipos_modalidade
FROM licitacoes
WHERE ativo = TRUE
GROUP BY uf
ORDER BY total DESC;
```

### Ver itens mais comuns

```sql
SELECT 
    item_categoria,
    COUNT(*) as qtd_licitacoes,
    SUM(valor_total_estimado) as valor_total
FROM licitacao_itens
GROUP BY item_categoria
ORDER BY qtd_licitacoes DESC
LIMIT 20;
```

## 🔄 MIGRAR DADOS DO SQLITE (Opcional)

Se você já tem dados no SQLite e quer migrar:

### Opção 1: Exportar e Importar (Recomendado)

```bash
# Executar o novo script que já popula o MySQL
python consulta_licitacao_mysql.py
```

### Opção 2: Script de Migração Manual

Crie um script `migrar_sqlite_para_mysql.py`:

```python
import sqlite3
import mysql.connector
import json
import uuid

# Conectar ao SQLite
sqlite_conn = sqlite3.connect('licitacoes.db')
sqlite_cursor = sqlite_conn.cursor()

# Conectar ao MySQL
mysql_conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='allmight'
)
mysql_cursor = mysql_conn.cursor()

# Buscar ID da fonte PNCP
mysql_cursor.execute("SELECT id FROM fontes_licitacao WHERE tipo_portal = 'PNCP'")
fonte_id = mysql_cursor.fetchone()[0]

# Migrar licitações
sqlite_cursor.execute("SELECT * FROM licitacoes")
for row in sqlite_cursor.fetchall():
    # Gerar UUID
    licitacao_uuid = str(uuid.uuid4())
    
    # Preparar dados e inserir no MySQL
    # ... (adaptar conforme necessário)

mysql_conn.commit()
print("✓ Migração concluída!")
```

## 📈 PRÓXIMOS PASSOS

### 1. Sistema de Matching IA

Implementar o motor de IA para gerar matches:

```python
# Exemplo futuro
from allmight_ia import gerar_matches

# Gerar matches para todas as licitações abertas
matches = gerar_matches(
    empresa_id='uuid-da-empresa',
    score_minimo=60
)
```

### 2. API REST

Criar API para acessar os dados:

```python
# FastAPI ou Flask
@app.get("/licitacoes/abertas")
def listar_licitacoes_abertas():
    return query_view("v_licitacoes_abertas")
```

### 3. Dashboard

Interface web para visualizar:
- Licitações abertas
- Matches gerados
- Propostas em andamento
- Estatísticas

### 4. Sistema de Notificações

Alertas automáticos quando:
- Novo match com score alto
- Prazo de licitação encerrando
- Resultado publicado

## 🛠️ TROUBLESHOOTING

### Erro: "Can't connect to MySQL server"

**Solução:**
1. Verifique se o MySQL está rodando no XAMPP
2. Teste a conexão no phpMyAdmin

### Erro: "Table 'allmight.licitacoes' doesn't exist"

**Solução:**
Execute o arquivo `schema_allmight_mysql.sql` no phpMyAdmin

### Erro: "Access denied for user 'root'"

**Solução:**
Verifique a senha do MySQL no XAMPP. Se houver senha, edite em `consulta_licitacao_mysql.py`:

```python
MYSQL_CONFIG = {
    'password': 'sua_senha_aqui',  # Mude aqui
}
```

### Script muito lento

**Solução:**
1. Use apenas DF para testes (opção 1)
2. Aumente o número de threads:
```python
MAX_THREADS = 30  # Aumentar
```

### Downloads falhando

**Solução:**
1. Desative downloads temporariamente:
```python
FAZER_DOWNLOAD_ARQUIVOS = False
```
2. Verifique conexão com internet
3. Aumente o timeout:
```python
DOWNLOAD_TIMEOUT = 60  # 60 segundos
```

## 📚 REFERÊNCIAS

- **MySQL Connector**: https://dev.mysql.com/doc/connector-python/en/
- **PNCP API**: https://pncp.gov.br/api
- **phpMyAdmin**: http://localhost/phpmyadmin
- **XAMPP Docs**: https://www.apachefriends.org/docs/

## 💡 DICAS

1. **Backup Regular**: Exporte o banco MySQL regularmente
2. **Índices**: O schema já tem índices otimizados
3. **Performance**: Use as views para queries complexas
4. **JSON**: Dados completos ficam em `dados_completos_json`
5. **UUIDs**: Todos os IDs principais usam UUID v4

## ✨ MELHORIAS FUTURAS

- [ ] Interface web (Django/Flask/FastAPI)
- [ ] Sistema de usuários e autenticação
- [ ] Motor de IA para matching
- [ ] Análise comercial automática
- [ ] Geração automática de propostas
- [ ] Dashboard com gráficos
- [ ] Exportação para Excel/PDF
- [ ] API REST completa
- [ ] Webhooks para notificações
- [ ] Integração com outros portais (ComprasNet, BEC, BLL)

---

**📧 Suporte**: Em caso de dúvidas, verifique os logs do script e do MySQL

**🎉 Pronto!** Seu sistema agora usa MySQL e está preparado para escalar!
