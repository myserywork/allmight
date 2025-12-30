-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 30/12/2025 às 23:09
-- Versão do servidor: 10.4.27-MariaDB
-- Versão do PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `allmight`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `fontes_licitacao`
--

CREATE TABLE `fontes_licitacao` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `url_base` varchar(255) NOT NULL,
  `url_api` varchar(255) DEFAULT NULL,
  `tipo_portal` enum('PNCP','COMPRASNET','BEC','BLL','PREFEITURA','ESTADUAL','OUTROS') NOT NULL,
  `formato_dados` enum('API_REST','API_SOAP','WEB_SCRAPING','RSS','XML') DEFAULT 'API_REST',
  `requer_autenticacao` tinyint(1) DEFAULT 0,
  `tipo_autenticacao` varchar(50) DEFAULT NULL,
  `credenciais` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Credenciais criptografadas' CHECK (json_valid(`credenciais`)),
  `intervalo_atualizacao_horas` int(11) DEFAULT 24,
  `ultima_coleta` timestamp NULL DEFAULT NULL,
  `proxima_coleta` timestamp NULL DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `status_conexao` enum('ONLINE','OFFLINE','ERRO','MANUTENCAO') DEFAULT 'ONLINE',
  `mensagem_erro` text DEFAULT NULL,
  `total_licitacoes_coletadas` int(11) DEFAULT 0,
  `ultima_quantidade_coletada` int(11) DEFAULT 0,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fontes de dados de licitações';

--
-- Despejando dados para a tabela `fontes_licitacao`
--

INSERT INTO `fontes_licitacao` (`id`, `nome`, `descricao`, `url_base`, `url_api`, `tipo_portal`, `formato_dados`, `requer_autenticacao`, `tipo_autenticacao`, `credenciais`, `intervalo_atualizacao_horas`, `ultima_coleta`, `proxima_coleta`, `ativo`, `status_conexao`, `mensagem_erro`, `total_licitacoes_coletadas`, `ultima_quantidade_coletada`, `data_cadastro`, `data_atualizacao`) VALUES
(1, 'PNCP - Portal Nacional de Contratações Públicas', 'Portal oficial do governo federal para licitações públicas', 'https://pncp.gov.br', 'https://pncp.gov.br/api', 'PNCP', 'API_REST', 0, NULL, NULL, 24, NULL, NULL, 1, 'ONLINE', NULL, 0, 0, '2025-12-02 15:23:48', '2025-12-02 15:23:48');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `fontes_licitacao`
--
ALTER TABLE `fontes_licitacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ativo` (`ativo`),
  ADD KEY `idx_proxima_coleta` (`proxima_coleta`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `fontes_licitacao`
--
ALTER TABLE `fontes_licitacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
