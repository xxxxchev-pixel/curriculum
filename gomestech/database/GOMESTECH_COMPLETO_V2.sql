
-- ===============================================
-- GomesTech - BASE DE DADOS COMPLETA (corrigida)
-- ===============================================
SET FOREIGN_KEY_CHECKS=0;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE DATABASE IF NOT EXISTS `gomestech` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gomestech`;


-- Remover views antes das tabelas (evita erro de dependência)
DROP VIEW IF EXISTS `v_produtos_populares`;
DROP VIEW IF EXISTS `v_stats_encomendas`;

-- Remover triggers antes das tabelas (evita erro)
DROP TRIGGER IF EXISTS `after_encomenda_item_insert`;
DROP TRIGGER IF EXISTS `after_encomenda_cancelada`;
DROP TRIGGER IF EXISTS `before_produto_insert`;

-- Remover tabelas em ordem reversa de dependência
DROP TABLE IF EXISTS `encomenda_itens`;
DROP TABLE IF EXISTS `encomendas`;
DROP TABLE IF EXISTS `carrinho`;
DROP TABLE IF EXISTS `favoritos`;
DROP TABLE IF EXISTS `comparacao`;
DROP TABLE IF EXISTS `produtos`;
DROP TABLE IF EXISTS `sessoes`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_admin` (`is_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User admin padrão (password: admin123)
INSERT INTO `users` (`id`, `nome`, `email`, `password`, `is_admin`) VALUES
(1, 'Administrador', 'admin@gomestech.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);


DROP TABLE IF EXISTS `produtos`;

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) DEFAULT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo` varchar(255) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `categoria` varchar(100) NOT NULL,
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `imagem` varchar(255) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `loja` varchar(100) DEFAULT 'GomesTech',
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_marca` (`marca`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_preco` (`preco`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `favoritos`;

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorito` (`user_id`,`produto_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `fk_favoritos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_favoritos_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `comparacao`;

CREATE TABLE `comparacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_comparacao` (`user_id`,`produto_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `fk_comparacao_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comparacao_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS=1;
DROP TABLE IF EXISTS `carrinho`;

CREATE TABLE `carrinho` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`,`produto_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `fk_carrinho_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_carrinho_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: encomendas (Pedidos)
-- =====================================================

DROP TABLE IF EXISTS `encomendas`;

CREATE TABLE `encomendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `morada` text NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `status` enum('pendente','processando','enviada','entregue','cancelada') DEFAULT 'pendente',
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_encomendas_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: encomenda_itens (Itens de Encomenda)
-- =====================================================

DROP TABLE IF EXISTS `encomenda_itens`;

CREATE TABLE `encomenda_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `encomenda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_encomenda` (`encomenda_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `fk_itens_encomenda` FOREIGN KEY (`encomenda_id`) REFERENCES `encomendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: sessoes (Sessões de utilizadores - opcional)
-- =====================================================

DROP TABLE IF EXISTS `sessoes`;

CREATE TABLE `sessoes` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `data` text NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VIEWS ÚTEIS
-- =====================================================

-- View: Produtos mais vendidos
DROP VIEW IF EXISTS `v_produtos_populares`;
CREATE VIEW `v_produtos_populares` AS
SELECT 
    p.id,
    p.marca,
    p.modelo,
    p.slug,
    p.preco,
    p.imagem,
    COUNT(ei.id) as total_vendas
FROM produtos p
LEFT JOIN encomenda_itens ei ON p.id = ei.produto_id
GROUP BY p.id
ORDER BY total_vendas DESC;

-- View: Estatísticas de encomendas
DROP VIEW IF EXISTS `v_stats_encomendas`;
CREATE VIEW `v_stats_encomendas` AS
SELECT 
    DATE(created_at) as data,
    COUNT(*) as total_encomendas,
    SUM(total) as receita_total,
    AVG(total) as ticket_medio
FROM encomendas
WHERE status != 'cancelada'
GROUP BY DATE(created_at)
ORDER BY data DESC;

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================

-- Índice composto para pesquisas comuns
ALTER TABLE `produtos` ADD INDEX `idx_categoria_ativo` (`categoria`, `ativo`);
ALTER TABLE `produtos` ADD INDEX `idx_marca_categoria` (`marca`, `categoria`);

-- Índice para ordenação por preço + categoria
ALTER TABLE `produtos` ADD INDEX `idx_categoria_preco` (`categoria`, `preco`);

-- =====================================================
-- TRIGGERS (Automação)
-- =====================================================

-- Trigger: Atualizar stock após criar item de encomenda
DROP TRIGGER IF EXISTS `after_encomenda_item_insert`;
DELIMITER $$
CREATE TRIGGER `after_encomenda_item_insert` 
AFTER INSERT ON `encomenda_itens`
FOR EACH ROW
BEGIN
    UPDATE produtos 
    SET stock = stock - NEW.qty 
    WHERE id = NEW.produto_id;
END$$
DELIMITER ;

-- Trigger: Restaurar stock após cancelar encomenda
DROP TRIGGER IF EXISTS `after_encomenda_cancelada`;
DELIMITER $$
CREATE TRIGGER `after_encomenda_cancelada` 
AFTER UPDATE ON `encomendas`
FOR EACH ROW
BEGIN
    IF NEW.status = 'cancelada' AND OLD.status != 'cancelada' THEN
        UPDATE produtos p
        INNER JOIN encomenda_itens ei ON p.id = ei.produto_id
        SET p.stock = p.stock + ei.qty
        WHERE ei.encomenda_id = NEW.id;
    END IF;
END$$
DELIMITER ;

-- Trigger: Auto-gerar SKU se não fornecido
DROP TRIGGER IF EXISTS `before_produto_insert`;
DELIMITER $$
CREATE TRIGGER `before_produto_insert` 
BEFORE INSERT ON `produtos`
FOR EACH ROW
BEGIN
    IF NEW.sku IS NULL OR NEW.sku = '' THEN
        SET NEW.sku = CONCAT('SKU-', LPAD(NEW.id, 6, '0'));
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- STORED PROCEDURES (Funcionalidades Avançadas)
-- =====================================================

-- Procedure: Criar encomenda completa
DROP PROCEDURE IF EXISTS `sp_criar_encomenda`;
DELIMITER $$
CREATE PROCEDURE `sp_criar_encomenda`(
    IN p_user_id INT,
    IN p_morada TEXT,
    IN p_telefone VARCHAR(20),
    OUT p_encomenda_id INT
)
BEGIN
    DECLARE v_total DECIMAL(10,2);
    
    -- Calcular total do carrinho
    SELECT SUM(p.preco * c.qty) INTO v_total
    FROM carrinho c
    INNER JOIN produtos p ON c.produto_id = p.id
    WHERE c.user_id = p_user_id;
    
    -- Criar encomenda
    INSERT INTO encomendas (user_id, total, morada, telefone, status)
    VALUES (p_user_id, v_total, p_morada, p_telefone, 'pendente');
    
    SET p_encomenda_id = LAST_INSERT_ID();
    
    -- Copiar itens do carrinho para encomenda
    INSERT INTO encomenda_itens (encomenda_id, produto_id, qty, preco)
    SELECT p_encomenda_id, c.produto_id, c.qty, p.preco
    FROM carrinho c
    INNER JOIN produtos p ON c.produto_id = p.id
    WHERE c.user_id = p_user_id;
    
    -- Limpar carrinho
    DELETE FROM carrinho WHERE user_id = p_user_id;
END$$
DELIMITER ;

-- Procedure: Pesquisa fulltext (simulada)
DROP PROCEDURE IF EXISTS `sp_pesquisar_produtos`;
DELIMITER $$
CREATE PROCEDURE `sp_pesquisar_produtos`(
    IN p_termo VARCHAR(255)
)
BEGIN
    SELECT 
        id,
        marca,
        modelo,
        slug,
        preco,
        imagem,
        categoria,
        CASE
            WHEN marca LIKE CONCAT(p_termo, '%') THEN 1
            WHEN modelo LIKE CONCAT(p_termo, '%') THEN 2
            WHEN marca LIKE CONCAT('%', p_termo, '%') THEN 3
            WHEN modelo LIKE CONCAT('%', p_termo, '%') THEN 4
            ELSE 5
        END as relevancia
    FROM produtos
    WHERE 
        ativo = 1 AND (
            marca LIKE CONCAT('%', p_termo, '%') OR
            modelo LIKE CONCAT('%', p_termo, '%') OR
            descricao LIKE CONCAT('%', p_termo, '%')
        )
    ORDER BY relevancia, marca, modelo
    LIMIT 50;
END$$
DELIMITER ;

-- =====================================================
-- CONFIGURAÇÕES FINAIS
-- =====================================================

-- Otimizar todas as tabelas
OPTIMIZE TABLE users, produtos, favoritos, comparacao, carrinho, encomendas, encomenda_itens;

-- Analisar tabelas para melhor performance
ANALYZE TABLE users, produtos, favoritos, comparacao, carrinho, encomendas, encomenda_itens;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================
-- FIM DA BASE DE DADOS
-- =====================================================

-- PRÓXIMOS PASSOS:
-- 1. Importar este ficheiro no phpMyAdmin
-- 2. Executar: database/importar_catalogo_json.php (se tiver produtos)
-- 3. Executar: database/gerar_slugs.php (para criar slugs únicos)
-- 4. Executar: database/atualizar_todos_precos.php (para preços realistas)
-- 5. Testar login com: admin@gomestech.pt / admin123
