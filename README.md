# 🦸 Allmight - Sistema de Gestão de Licitações

Sistema inteligente para monitoramento, análise e geração de propostas para licitações públicas brasileiras.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat-square&logo=php&logoColor=white)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3.x-EF4223?style=flat-square&logo=codeigniter&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)

## 📋 Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Funcionalidades](#-funcionalidades)
- [Tecnologias](#-tecnologias)
- [Instalação](#-instalação)
- [Configuração](#-configuração)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [API Integrations](#-api-integrations)
- [Licença](#-licença)

## 🎯 Sobre o Projeto

O **Allmight** é uma plataforma completa para gestão de licitações públicas que permite:

- 📊 **Monitoramento Inteligente**: Acompanhe licitações relevantes para seu negócio através de palavras-chave configuráveis
- 🤖 **IA Integrada**: Geração automática de propostas comerciais usando Google Gemini
- 📁 **Gestão de Documentos**: Download, extração e análise automática de editais e anexos
- 🏢 **Multi-empresa**: Gerencie múltiplas empresas e seus documentos de habilitação
- ⚡ **Alertas em Tempo Real**: Receba notificações sobre licitações que correspondem ao perfil da sua empresa

## ✨ Funcionalidades

### 🔍 Monitoramento de Licitações
- Busca automática por palavras-chave
- Filtros por valor, modalidade, região e situação
- Score de relevância baseado em IA
- Dashboard com estatísticas em tempo real

### 📄 Gestão de Documentos
- Download automático de editais e anexos
- Extração de arquivos ZIP (incluindo ZIPs aninhados)
- Extração de texto de PDFs com OCR
- Organização por licitação

### 🤖 Geração de Propostas com IA
- Análise completa dos documentos da licitação
- Geração de propostas técnicas e comerciais
- Formatação profissional em HTML
- Integração com dados cadastrais da empresa

### 🏢 Gestão de Empresas
- Cadastro completo com CNPJ, endereço, etc.
- Gestão de documentos de habilitação
- Controle de validade de certidões
- Configuração de palavras-chave para monitoramento

## 🛠 Tecnologias

### Backend
- **PHP 7.4+** com CodeIgniter 3
- **MySQL 8.0+** para persistência
- **Composer** para gerenciamento de dependências

### Frontend
- **TailwindCSS 3** para estilização
- **Alpine.js** para interatividade
- **Font Awesome** para ícones

### Integrações
- **Google Gemini API** para IA generativa
- **PNCP (Portal Nacional de Contratações Públicas)** para dados de licitações
- **smalot/pdfparser** para extração de texto de PDFs

## 📦 Instalação

### Pré-requisitos
- PHP 7.4 ou superior
- MySQL 8.0 ou superior
- Composer
- Extensões PHP: curl, json, mbstring, zip

### Passos

1. **Clone o repositório**
```bash
git clone https://github.com/myserywork/allmight.git
cd allmight
```

2. **Instale as dependências**
```bash
composer install
```

3. **Configure o banco de dados**
```bash
# Importe o schema
mysql -u root -p < schema_allmight_mysql.sql
```

4. **Configure o ambiente**
```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Edite com suas configurações
nano .env
```

5. **Configure o Apache/Nginx**
```apache
# Aponte o DocumentRoot para a pasta do projeto
DocumentRoot "/path/to/allmight"
```

## ⚙️ Configuração

### Arquivo `.env`

```env
# API Keys
GEMINI_API_KEY=sua_chave_api_aqui

# Banco de Dados
DB_HOST=localhost
DB_USER=root
DB_PASS=sua_senha
DB_NAME=allmight

# Ambiente
ENVIRONMENT=development
```

### Banco de Dados

Edite `application/config/database.php` ou use as variáveis de ambiente:

```php
$db['default'] = array(
    'hostname' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'database' => getenv('DB_NAME') ?: 'allmight',
    // ...
);
```

## 📁 Estrutura do Projeto

```
allmight/
├── application/
│   ├── config/          # Configurações do CodeIgniter
│   ├── controllers/     # Controllers (Admin.php principal)
│   ├── models/          # Models de dados
│   │   ├── Licitacao_model.php
│   │   ├── Empresa_model.php
│   │   ├── Proposta_model.php
│   │   ├── Arquivo_model.php
│   │   └── Alerta_model.php
│   ├── views/           # Views (templates)
│   │   └── admin/       # Área administrativa
│   └── helpers/         # Helpers customizados
├── python/              # Scripts auxiliares Python
├── uploads/             # Arquivos enviados
│   ├── documentos/      # Documentos das licitações
│   └── logos/           # Logos das empresas
├── .env                 # Variáveis de ambiente (não versionado)
├── .env.example         # Exemplo de configuração
└── schema_allmight_mysql.sql  # Schema do banco
```

## 🔌 API Integrations

### Google Gemini

O sistema utiliza o modelo `gemini-2.0-flash-exp` para:
- Geração de keywords baseadas no perfil da empresa
- Análise de documentos de licitação
- Geração de propostas comerciais

### PNCP

Integração com o Portal Nacional de Contratações Públicas para:
- Busca de licitações
- Download de documentos
- Atualização de status

## 🚀 Uso

### Acessar o sistema
```
http://localhost/allmight/admin
```

### Fluxo básico

1. **Cadastre uma empresa** com seus dados e palavras-chave
2. **Execute o monitoramento** para buscar licitações relevantes
3. **Analise os alertas** gerados pelo sistema
4. **Gere propostas** automaticamente com IA

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](license.txt) para mais detalhes.

## 👥 Contribuição

Contribuições são bem-vindas! Por favor, leia as diretrizes de contribuição antes de enviar um PR.

## 📧 Contato

- **Desenvolvedor**: myserywork
- **GitHub**: [@myserywork](https://github.com/myserywork)

---

⭐ Se este projeto foi útil para você, considere dar uma estrela no repositório!
