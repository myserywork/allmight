# 📘 Worker de Licitações - Guia Completo para Dev Junior

## 🎯 O que é e como funciona?

O **worker_licitacao.py** é um robô que roda 24/7 no servidor coletando licitações automaticamente todo dia às 3h da manhã.

### Por que transformamos em Worker?

**Antes (Script Manual):**
- ❌ Você tinha que rodar manualmente todo dia
- ❌ Perguntava "qual estado coletar?" toda vez
- ❌ Se fechasse o terminal, parava
- ❌ Prints não ficavam salvos

**Agora (Worker Automático):**
- ✅ Roda sozinho todo dia às 3h
- ✅ Sempre coleta TODOS os estados
- ✅ Continua rodando mesmo sem terminal
- ✅ Tudo é salvo em logs rotativos

---

## 🏗️ Arquitetura do Worker

### 1️⃣ **Sistema de Logs** (em vez de print)
```python
logger.info("Isso vai pro arquivo de log")   # Informação normal
logger.warning("Atenção!")                   # Aviso importante
logger.error("Deu erro!")                    # Erro que precisa ver
```

**Onde ficam os logs?**
- Arquivo: `logs/worker_licitacao.log`
- Tamanho máximo: 10MB (depois rotaciona)
- Mantém 5 backups: `.log.1`, `.log.2`, etc.

### 2️⃣ **Função job()** - O coração do Worker
```python
def job():
    """Executa coleta completa"""
    # 1. Verifica se já rodou hoje
    # 2. Conecta no banco
    # 3. Coleta TODOS os 27 estados
    # 4. Salva tudo no MySQL
    # 5. Marca licitações encerradas
```

### 3️⃣ **Agendamento com schedule**
```python
schedule.every().day.at("03:00").do(job)  # Roda às 3h da manhã
```

### 4️⃣ **Loop Infinito** (mantém worker vivo)
```python
while True:
    schedule.run_pending()  # Executa job se chegou a hora
    time.sleep(60)          # Espera 1 minuto e verifica de novo
```

---

## 🔧 Como Instalar e Rodar

### Passo 1: Instalar bibliotecas novas

**No Windows (você já tem venv):**
```powershell
cd c:\xampp\htdocs\allmight\python
.\venv\Scripts\Activate.ps1
pip install schedule
```

**No Linux:**
```bash
cd /caminho/allmight/python
source venv/bin/activate
pip install schedule
```

### Passo 2: Testar antes de deixar rodando

**Executar UMA VEZ manualmente (modo teste):**
```powershell
# Windows
cd c:\xampp\htdocs\allmight\python
.\venv\Scripts\Activate.ps1
python worker_licitacao.py --now
```

Isso vai:
- ✅ Executar coleta imediatamente (não espera 3h)
- ✅ Mostrar logs no terminal E salvar no arquivo
- ✅ Você vê se tudo funciona antes de deixar 24/7

### Passo 3: Rodar em Background (24/7)

#### **No Windows:**

**Opção 1 - Task Scheduler (Recomendado):**
1. Abra "Agendador de Tarefas"
2. Criar Tarefa Básica
3. Nome: "Worker Licitações AllMight"
4. Disparador: "Quando o computador iniciar"
5. Ação: "Iniciar um programa"
   - Programa: `C:\xampp\htdocs\allmight\python\venv\Scripts\python.exe`
   - Argumentos: `worker_licitacao.py`
   - Iniciar em: `C:\xampp\htdocs\allmight\python`
6. Marcar: "Executar independente de usuário estar logado"

**Opção 2 - NSSM (Serviço Windows):**
```powershell
# Baixe NSSM: https://nssm.cc/download
nssm install WorkerLicitacao "C:\xampp\htdocs\allmight\python\venv\Scripts\python.exe"
nssm set WorkerLicitacao AppDirectory "C:\xampp\htdocs\allmight\python"
nssm set WorkerLicitacao AppParameters "worker_licitacao.py"
nssm start WorkerLicitacao
```

#### **No Linux (Servidor de Produção):**

**Criar Systemd Service:**
```bash
sudo nano /etc/systemd/system/worker-licitacao.service
```

Cole isso:
```ini
[Unit]
Description=Worker de Licitações AllMight
After=network.target mysql.service

[Service]
Type=simple
User=seu_usuario
WorkingDirectory=/var/www/allmight/python
ExecStart=/var/www/allmight/python/venv/bin/python worker_licitacao.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Ativar e iniciar:
```bash
sudo systemctl daemon-reload
sudo systemctl enable worker-licitacao.service
sudo systemctl start worker-licitacao.service
```

Ver logs em tempo real:
```bash
sudo journalctl -u worker-licitacao -f
```

---

## 📊 Como Monitorar o Worker

### Ver logs em tempo real:

**Windows:**
```powershell
Get-Content c:\xampp\htdocs\allmight\python\logs\worker_licitacao.log -Wait -Tail 50
```

**Linux:**
```bash
tail -f /var/www/allmight/python/logs/worker_licitacao.log
```

### Ver status no Linux:
```bash
sudo systemctl status worker-licitacao
```

### Parar/Reiniciar worker:

**Windows (Task Scheduler):**
- Abrir "Agendador de Tarefas"
- Clicar direito na tarefa → Parar/Executar

**Linux:**
```bash
sudo systemctl stop worker-licitacao    # Parar
sudo systemctl start worker-licitacao   # Iniciar
sudo systemctl restart worker-licitacao # Reiniciar
```

---

## 🔍 Diferenças principais do código original

| Característica | Script Manual | Worker 24/7 |
|---|---|---|
| **Interatividade** | `input()` pergunta estado | Remove todos `input()` |
| **Estados** | Você escolhe DF ou Todos | SEMPRE todos os 27 estados |
| **Execução** | Roda 1 vez e para | Roda TODO DIA às 3h |
| **Output** | `print()` no terminal | `logger.info()` em arquivo |
| **Backup JSON** | Gera arquivo gigante | NÃO gera (dados no MySQL) |
| **Coleta repetida** | Pergunta se continua | Verifica automaticamente |
| **Conexão MySQL** | Aberta no início | Abre/fecha a cada job |
| **Parada** | Ctrl+C para | Roda até você parar serviço |

---

## ⚠️ Pontos de Atenção

### 1. Ambiente Virtual
- ✅ **SIM**, o worker usa o venv que você já tem
- ✅ Ao configurar serviço, use caminho completo: `venv/Scripts/python.exe`
- ✅ Todas as libs instaladas no venv funcionam normalmente

### 2. MySQL precisa estar rodando
- Worker precisa do MySQL ativo 24/7
- No Windows: XAMPP deve iniciar com o sistema
- No Linux: MySQL como serviço (`systemctl enable mysql`)

### 3. Horário configurável
Mude no código:
```python
HORARIO_COLETA = "03:00"  # Formato 24h: HH:MM
```

### 4. Threads ajustadas
Configuração segura para notebooks (já está no worker):
```python
MAX_THREADS = 6              # Estados simultâneos
MAX_LICITACAO_THREADS = 4    # Licitações por estado
MAX_DOWNLOAD_THREADS = 12    # Downloads de arquivos
```

---

## 🐛 Troubleshooting

**Worker não inicia:**
```bash
# Ver erro específico nos logs
tail -100 logs/worker_licitacao.log
```

**Conexão MySQL falha:**
- Verificar se MySQL está rodando
- Testar credenciais no código: `user`, `password`, `database`

**Worker roda mas não coleta:**
- Ver se já coletou hoje (verifica se tem +100 licitações atualizadas hoje)
- Executar com `--now` para forçar coleta imediata

**Consumo alto de memória:**
- Reduzir threads: `MAX_THREADS = 4`, `MAX_LICITACAO_THREADS = 2`
- Desabilitar downloads: `FAZER_DOWNLOAD_ARQUIVOS = False`

---

## 📦 Resumo de Instalação Rápida

```bash
# 1. Ativar venv
cd c:\xampp\htdocs\allmight\python
.\venv\Scripts\Activate.ps1

# 2. Instalar schedule
pip install schedule

# 3. Testar worker manualmente
python worker_licitacao.py --now

# 4. Configurar para rodar 24/7 (escolher Windows ou Linux acima)
# 5. Monitorar logs
Get-Content logs\worker_licitacao.log -Wait -Tail 50
```

---

## ✅ Checklist Final

- [ ] Backup do script original criado (`.manual_backup`)
- [ ] Biblioteca `schedule` instalada no venv
- [ ] Worker testado com `--now` e funcionou
- [ ] Serviço configurado no Windows/Linux
- [ ] MySQL configurado para iniciar automaticamente
- [ ] Logs sendo gerados em `logs/worker_licitacao.log`
- [ ] Worker rodando e aguardando horário agendado

**Pronto! Seu worker está rodando 24/7 coletando licitações automaticamente! 🚀**
