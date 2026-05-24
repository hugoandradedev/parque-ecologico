-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql305.infinityfree.com
-- Tempo de geração: 19/05/2026 às 15:51
-- Versão do servidor: 11.4.10-MariaDB
-- Versão do PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `if0_41837589_parque`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamento`
--

CREATE TABLE `agendamento` (
  `id` int(11) NOT NULL,
  `nome_responsavel` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `data_reserva` date NOT NULL,
  `qtd_visitantes` tinyint(4) NOT NULL,
  `horario_entrada` time NOT NULL,
  `horario_saida` time NOT NULL,
  `status` enum('pendente','aprovado','rejeitado','cancelado') DEFAULT 'pendente',
  `quiosque_id` int(11) NOT NULL DEFAULT 1,
  `aceite_termos` tinyint(1) NOT NULL DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `bloqueios`
--

CREATE TABLE `bloqueios` (
  `id` int(11) NOT NULL,
  `data_bloqueada` date NOT NULL,
  `motivo` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `email` varchar(255) NOT NULL,
  `nome_responsavel` varchar(255) NOT NULL,
  `nome_instituicao` varchar(255) DEFAULT NULL,
  `nome_diretor` varchar(255) DEFAULT NULL,
  `telefone` varchar(50) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `guias`
--

CREATE TABLE `guias` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `especialidade` varchar(150) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `id` int(11) NOT NULL,
  `nome` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `assunto` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp(),
  `lida` tinyint(1) NOT NULL DEFAULT 0,
  `respondida` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `nome_usuario` varchar(30) DEFAULT NULL,
  `sobrenome_usuario` varchar(255) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `login_usuario` varchar(50) NOT NULL,
  `senha_usuario` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('cliente','admin') NOT NULL DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`nome_usuario`, `sobrenome_usuario`, `id`, `login_usuario`, `senha_usuario`, `criado_em`, `tipo`) VALUES
(NULL, NULL, 1, 'admin', '$2y$10$Sj6kAfzRIKDAyRUTLuV7l.AxxITC0bCRzULF/Vp6SuI2TOCPhntGu', '2026-05-01 02:39:23', 'admin');
-- --------------------------------------------------------

--
-- Estrutura para tabela `visita_tecnica`
--

CREATE TABLE `visita_tecnica` (
  `id` int(11) NOT NULL,
  `nome_instituicao` varchar(255) NOT NULL,
  `nome_diretor` varchar(255) DEFAULT NULL,
  `nome_responsavel` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `data_visita` date NOT NULL,
  `horario_entrada` time NOT NULL,
  `horario_saida` time NOT NULL,
  `qtd_visitantes` int(11) NOT NULL,
  `faixa_etaria` varchar(50) DEFAULT NULL,
  `objetivo` text NOT NULL,
  `observacoes` text DEFAULT NULL,
  `guia` varchar(100) NOT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `guia_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `agendamento`
--
ALTER TABLE `agendamento`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `bloqueios`
--
ALTER TABLE `bloqueios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `data_bloqueada` (`data_bloqueada`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`email`);

--
-- Índices de tabela `guias`
--
ALTER TABLE `guias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login_usuario` (`login_usuario`);

--
-- Índices de tabela `visita_tecnica`
--
ALTER TABLE `visita_tecnica`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_guia` (`guia_id`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `agendamento`
--
ALTER TABLE `agendamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `bloqueios`
--
ALTER TABLE `bloqueios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `guias`
--
ALTER TABLE `guias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `visita_tecnica`
--
ALTER TABLE `visita_tecnica`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
