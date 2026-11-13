-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 13/11/2026 às 11:12
-- Versão do servidor: 8.4.7
-- Versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `d20realitas`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `campanha`
--

DROP TABLE IF EXISTS `campanha`;
CREATE TABLE IF NOT EXISTS `campanha` (
  `id_campanha` int NOT NULL AUTO_INCREMENT,
  `nome_campanha` varchar(150) NOT NULL,
  `data_criacao` date DEFAULT NULL,
  `id_regras` int DEFAULT '0',
  `descricao_campanha` text,
  `id_usuario` int NOT NULL,
  `token_convite` varchar(64) DEFAULT NULL,
  `foto_campanha` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_campanha`),
  UNIQUE KEY `token_convite` (`token_convite`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `campanha`
--

INSERT INTO `campanha` (`id_campanha`, `nome_campanha`, `data_criacao`, `id_regras`, `descricao_campanha`, `id_usuario`, `token_convite`, `foto_campanha`) VALUES
(3, 'oioi', NULL, 0, 'oioi\r\n', 1, '1c5879503279e15d93f79bad5c752b50', '1794533397_6af66815e95f1.jpg'),
(5, 'aaaaaaa', NULL, 0, 'aa', 5, 'f2232794cf3cc5ed1b9dfc5426a5e274', NULL),
(6, 'aaaaaaaaaaaaaaaaaaa', NULL, 0, 'aaaaaaaaaaaaaaaaaa', 5, '2febd5d32b6f3ddaa813181cb159c60b', '1793204558_6ae2214e3f480.jpg'),
(7, 'aaaaaaa', NULL, 0, 'aaaaaaaaaaaa', 4, '1d25470b68b1167413fcbbf39487db6a', '1793195552_6ae1fe201c232.jpg'),
(8, 'aaaaaaaa', NULL, 0, 'aaaaaaaaaa', 7, 'a54ec7109b2146d05815a241455fb3bf', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `campanha_fichas`
--

DROP TABLE IF EXISTS `campanha_fichas`;
CREATE TABLE IF NOT EXISTS `campanha_fichas` (
  `id_ligacao` int NOT NULL AUTO_INCREMENT,
  `id_campanha` int NOT NULL,
  `id_usuario` int NOT NULL,
  `id_ficha` int NOT NULL,
  `data_entrada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ligacao`),
  KEY `id_campanha` (`id_campanha`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_ficha` (`id_ficha`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `campanha_fichas`
--

INSERT INTO `campanha_fichas` (`id_ligacao`, `id_campanha`, `id_usuario`, `id_ficha`, `data_entrada`) VALUES
(9, 3, 10, 25, '2026-11-13 01:25:56'),
(8, 3, 8, 24, '2026-11-06 17:48:11'),
(7, 6, 6, 21, '2026-10-28 16:22:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ficha_personagem`
--

DROP TABLE IF EXISTS `ficha_personagem`;
CREATE TABLE IF NOT EXISTS `ficha_personagem` (
  `id_ficha` int NOT NULL AUTO_INCREMENT,
  `nome_personagem` varchar(150) NOT NULL,
  `origem` varchar(100) DEFAULT NULL,
  `classe_ou_trilha` varchar(100) DEFAULT NULL,
  `pv_atual` int DEFAULT '20',
  `pv_maximo` int DEFAULT '20',
  `id_usuario` int NOT NULL,
  `id_campanha` int DEFAULT NULL,
  `atributos` varchar(255) DEFAULT NULL,
  `pericias` varchar(255) DEFAULT NULL,
  `foto_personagem` varchar(255) DEFAULT NULL,
  `aparencia` text,
  `personalidade` text,
  `historico` text,
  `objetivo` text,
  PRIMARY KEY (`id_ficha`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `ficha_personagem`
--

INSERT INTO `ficha_personagem` (`id_ficha`, `nome_personagem`, `origem`, `classe_ou_trilha`, `pv_atual`, `pv_maximo`, `id_usuario`, `id_campanha`, `atributos`, `pericias`, `foto_personagem`, `aparencia`, `personalidade`, `historico`, `objetivo`) VALUES
(20, 'jose henrique', 'Trabalhador Rural', 'Combatente', 20, 20, 5, NULL, 'AGI: 1, INT: 1, VIG: 2, PRE: 1, FOR: 1', 'Adestramento (+5), Sobrevivência (+5)', NULL, NULL, NULL, NULL, NULL),
(21, 'saori', 'Acadêmico', 'Ocultista', 20, 20, 6, NULL, 'AGI: 4, INT: 1, VIG: 4, PRE: 2, FOR: 0', 'Ciências (+5), Investigação (+5)', NULL, NULL, NULL, NULL, NULL),
(22, 'charmander', 'Artista', 'Ocultista', 20, 20, 1, NULL, 'AGI: 1, INT: 1, VIG: 1, PRE: 2, FOR: 1', '{\"Acrobacia\":{\"t\":10,\"o\":0},\"Artes\":{\"t\":5,\"o\":0},\"Enganação\":{\"t\":5,\"o\":0}}', '1793196863_6ae2033f42f99.jpg', NULL, NULL, NULL, NULL),
(23, 'manu suares', 'Criminoso', 'Ocultista', 32, 32, 7, NULL, 'AGI: 4, INT: 2, VIG: 1, PRE: 1, FOR: 1', 'Crime (+5), Furtividade (+5)', NULL, NULL, NULL, NULL, NULL),
(24, 'o', 'Artista', 'Ocultista', 20, 20, 8, NULL, 'AGI: 1, INT: 1, VIG: 1, PRE: 2, FOR: 1', '{\"Artes\":{\"t\":5,\"o\":0},\"Enganação\":{\"t\":5,\"o\":0},\"Vontade\":{\"t\":5,\"o\":0}}', NULL, NULL, NULL, NULL, NULL),
(25, 'aaaa', 'Artista', 'Ocultista', 20, 20, 10, NULL, 'AGI: 1, INT: 1, VIG: 1, PRE: 2, FOR: 1', 'Artes (+5), Enganação (+5)', NULL, NULL, NULL, NULL, NULL),
(26, 'aaaaaaaaa', 'Acadêmico', 'Especialista', 20, 20, 1, NULL, 'AGI: 2, INT: 3, VIG: 3, PRE: 1, FOR: 1', 'Ciências (+5), Investigação (+5)', NULL, 'aaaaa', 'aaaa', 'aaaaaaa', 'aaaaaaaaaaa'),
(27, 'jose henrique', '', 'Ocultista', 20, 20, 1, NULL, 'AGI: 1, INT: 1, VIG: 1, PRE: 1, FOR: 1', 'Nenhuma perícia base', '1794540304_6af68310ab407.webp', '', '', '', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `inventario`
--

DROP TABLE IF EXISTS `inventario`;
CREATE TABLE IF NOT EXISTS `inventario` (
  `id_inventario` int NOT NULL AUTO_INCREMENT,
  `id_ficha` int NOT NULL,
  `nome_item` varchar(100) NOT NULL,
  `descricao` text,
  `quantidade` int DEFAULT '1',
  PRIMARY KEY (`id_inventario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `regras`
--

DROP TABLE IF EXISTS `regras`;
CREATE TABLE IF NOT EXISTS `regras` (
  `id_regras` int NOT NULL AUTO_INCREMENT,
  `nome_regras` varchar(100) NOT NULL,
  `descricao` text,
  PRIMARY KEY (`id_regras`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `regras`
--

INSERT INTO `regras` (`id_regras`, `nome_regras`, `descricao`) VALUES
(4, 'regras adicionais..', 'aq voce pode adicionar regras, gasta 5 PE para pensar em uma regra');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nome_usuario` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_usuario` varchar(50) DEFAULT 'jogador',
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nome_usuario`, `email`, `senha`, `tipo_usuario`) VALUES
(1, 'jose', 'jose@gmail.com', '$2y$10$KrpGGgJ5hkAKzcYrTbPpGOFIFVBIeVKdUHdaim1TdtN.nogCqDJy2', 'jogador'),
(4, 'larissa', 'larissa@gmail.com', '$2y$10$16bta.UD6fGffkVWkfaKT.PJIxy4jRRdJw5zJcWhajwBd02gmsnte', 'jogador'),
(5, 'marco', 'marco@gmail.com', '$2y$10$fCjjavliRcR9Ih4knz4yj.1U8cNjHWqNlsi00HKH9yCU73nyhzG8q', 'jogador'),
(6, 'sauani', 'sauani@gmail.com', '$2y$10$A6J7Ynn7U1IDed/bbshH9e.KpiQniqTWHrwAci3t4OvSSLDWaNOSa', 'jogador'),
(7, 'Manu soares', 'ffmanu355@gmail.com', '$2y$10$DBLXmtK6TpSYVItKwsIVpuUTYO7uEBTI9Rbq1XBUVoRDa0uVcswZm', 'jogador'),
(8, 'loren', 'loren@gmail.com', '$2y$10$LLeU1aI6CgaI624Pse4eb.WnhmlrGP2JvD/sS3RRcld/oAAzM.V1e', 'jogador'),
(9, 'admin', 'admin@gmail.com', '$2y$10$I0DZ8VqKWqmo6tukyUmZ0O4qHk8rNoer5Z89migP2bqYQz7ERVGyC', 'admin'),
(10, 'Josue', 'josue@gmail.com', '$2y$10$CUZhBlueW51nNTTc0.o5ge.baw.y49Sk3KqUPL9LdKr7sg.yootkG', 'jogador');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
