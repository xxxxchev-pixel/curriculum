-- =============================================================================
-- GOMESTECH - BASE DE DADOS COMPLETA E OTIMIZADA
-- Versão Final - Consolidada e Limpa
-- Data: 03 Novembro 2025
-- =============================================================================

SET FOREIGN_KEY_CHECKS=0;
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- =============================================================================
-- CRIAR BASE DE DADOS
-- =============================================================================

DROP DATABASE IF EXISTS `gomestech`;
CREATE DATABASE `gomestech` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gomestech`;

-- =============================================================================
-- TABELA: users (Utilizadores)
-- =============================================================================

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `morada` TEXT DEFAULT NULL,
  `nif` VARCHAR(9) DEFAULT NULL,
  `codigo_postal` VARCHAR(8) DEFAULT NULL,
  `is_admin` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_nif` (`nif`),
  KEY `idx_admin` (`is_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin padrão (password: admin123)
INSERT INTO `users` (`id`, `nome`, `email`, `password`, `is_admin`) VALUES
(1, 'Administrador', 'admin@gomestech.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- =============================================================================
-- TABELA: categories (Categorias)
-- =============================================================================

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50) DEFAULT NULL,
  `parent_id` INT(11) DEFAULT NULL,
  `display_order` INT(11) DEFAULT 0,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_active` (`active`),
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorias principais
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `parent_id`, `display_order`, `active`) VALUES
(1, 'Smartphones', 'smartphones', '📱', NULL, 1, 1),
(2, 'Laptops', 'laptops', '💻', NULL, 2, 1),
(3, 'Tablets', 'tablets', '📱', NULL, 3, 1),
(4, 'Wearables', 'wearables', '⌚', NULL, 4, 1),
(5, 'TVs', 'tvs', '📺', NULL, 5, 1),
(6, 'Audio', 'audio', '🎧', NULL, 6, 1),
(7, 'Consolas', 'consolas', '🎮', NULL, 7, 1),
(8, 'Frigoríficos', 'frigorificos', '🧊', NULL, 8, 1),
(9, 'Máquinas de Lavar', 'maquinas-lavar', '🌀', NULL, 9, 1),
(10, 'Micro-ondas', 'micro-ondas', '📦', NULL, 10, 1),
(11, 'Aspiradores', 'aspiradores', '🧹', NULL, 11, 1),
(12, 'Ar Condicionado', 'ar-condicionado', '❄️', NULL, 12, 1),
(13, 'Máquinas de Café', 'maquinas-cafe', '☕', NULL, 13, 1);

-- =============================================================================
-- TABELA: brands (Marcas)
-- =============================================================================

DROP TABLE IF EXISTS `brands`;

CREATE TABLE `brands` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `logo` VARCHAR(500) DEFAULT NULL,
  `display_order` INT(11) DEFAULT 0,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Marcas principais
INSERT INTO `brands` (`id`, `name`, `slug`, `display_order`, `active`) VALUES
(1, 'Apple', 'apple', 1, 1),
(2, 'Samsung', 'samsung', 2, 1),
(3, 'Google', 'google', 3, 1),
(4, 'OnePlus', 'oneplus', 4, 1),
(5, 'Dell', 'dell', 5, 1),
(6, 'Lenovo', 'lenovo', 6, 1),
(7, 'HP', 'hp', 7, 1),
(8, 'Asus', 'asus', 8, 1),
(9, 'MSI', 'msi', 9, 1),
(10, 'Sony', 'sony', 10, 1),
(11, 'Microsoft', 'microsoft', 11, 1),
(12, 'Nintendo', 'nintendo', 12, 1),
(13, 'LG', 'lg', 13, 1),
(14, 'Philips', 'philips', 14, 1),
(15, 'TP-Link', 'tp-link', 15, 1),
(16, 'Xiaomi', 'xiaomi', 16, 1),
(17, 'Motorola', 'motorola', 17, 1),
(18, 'Realme', 'realme', 18, 1),
(19, 'Garmin', 'garmin', 19, 1),
(20, 'Bose', 'bose', 20, 1),
(21, 'JBL', 'jbl', 21, 1),
(22, 'Sonos', 'sonos', 22, 1),
(23, 'Marshall', 'marshall', 23, 1),
(24, 'Harman Kardon', 'harman-kardon', 24, 1),
(25, 'Amazon', 'amazon', 25, 1),
(26, 'Bosch', 'bosch', 26, 1),
(27, 'Siemens', 'siemens', 27, 1),
(28, 'Whirlpool', 'whirlpool', 28, 1),
(29, 'Beko', 'beko', 29, 1),
(30, 'Indesit', 'indesit', 30, 1),
(31, 'Teka', 'teka', 31, 1),
(32, 'Panasonic', 'panasonic', 32, 1),
(33, 'Dyson', 'dyson', 33, 1),
(34, 'Rowenta', 'rowenta', 34, 1),
(35, 'iRobot', 'irobot', 35, 1),
(36, 'Daikin', 'daikin', 36, 1),
(37, 'Mitsubishi', 'mitsubishi', 37, 1),
(38, 'Fujitsu', 'fujitsu', 38, 1),
(39, 'De\'Longhi', 'delonghi', 39, 1),
(40, 'Nespresso', 'nespresso', 40, 1),
(41, 'Sage', 'sage', 41, 1),
(42, 'Krups', 'krups', 42, 1);

-- =============================================================================
-- TABELA: produtos (Produtos)
-- =============================================================================

DROP TABLE IF EXISTS `produtos`;

CREATE TABLE `produtos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sku` VARCHAR(50) DEFAULT NULL,
  `marca` VARCHAR(100) NOT NULL,
  `modelo` VARCHAR(255) NOT NULL,
  `nome` VARCHAR(200) DEFAULT NULL,
  `slug` VARCHAR(250) DEFAULT NULL,
  `categoria` VARCHAR(100) NOT NULL,
  `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `preco_original` DECIMAL(10,2) DEFAULT NULL,
  `stock` INT(11) DEFAULT 100,
  `imagem` VARCHAR(500) DEFAULT NULL,
  `descricao` TEXT DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `loja` VARCHAR(100) DEFAULT 'GomesTech',
  `ativo` TINYINT(1) DEFAULT 1,
  `destaque` TINYINT(1) DEFAULT 0,
  `novidade` TINYINT(1) DEFAULT 0,
  `produto_dia` TINYINT(1) DEFAULT 0,
  `category_id` INT(11) DEFAULT NULL,
  `brand_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_marca` (`marca`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_preco` (`preco`),
  KEY `idx_destaque` (`destaque`),
  KEY `idx_novidade` (`novidade`),
  KEY `idx_produto_dia` (`produto_dia`),
  KEY `idx_categoria_ativo` (`categoria`, `ativo`),
  KEY `idx_marca_categoria` (`marca`, `categoria`),
  KEY `idx_categoria_preco` (`categoria`, `preco`),
  KEY `idx_category` (`category_id`),
  KEY `idx_brand` (`brand_id`),
  CONSTRAINT `fk_produtos_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_produtos_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: favoritos (Lista de Desejos)
-- =============================================================================

DROP TABLE IF EXISTS `favoritos`;

CREATE TABLE `favoritos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `produto_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorito` (`user_id`, `produto_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `fk_favoritos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_favoritos_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: comparacao (Comparação de Produtos)
-- =============================================================================

DROP TABLE IF EXISTS `comparacao`;

CREATE TABLE `comparacao` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `produto_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_comparacao` (`user_id`, `produto_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `fk_comparacao_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comparacao_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: carrinho (Carrinho de Compras)
-- =============================================================================

DROP TABLE IF EXISTS `carrinho`;

CREATE TABLE `carrinho` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `produto_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`, `produto_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `fk_carrinho_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_carrinho_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: encomendas (Pedidos/Encomendas)
-- =============================================================================

DROP TABLE IF EXISTS `encomendas`;

CREATE TABLE `encomendas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) DEFAULT NULL,
  `taxa_envio` DECIMAL(10,2) DEFAULT 0.00,
  `desconto` DECIMAL(10,2) DEFAULT 0.00,
  `iva` DECIMAL(10,2) DEFAULT 0.00,
  `morada` TEXT NOT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `metodo_pagamento` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('pendente','processando','enviada','entregue','cancelada') DEFAULT 'pendente',
  `notas` TEXT DEFAULT NULL,
  `tracking_code` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `shipped_at` TIMESTAMP NULL DEFAULT NULL,
  `delivered_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_encomendas_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: encomenda_itens (Itens de Encomenda)
-- =============================================================================

DROP TABLE IF EXISTS `encomenda_itens`;

CREATE TABLE `encomenda_itens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `encomenda_id` INT(11) NOT NULL,
  `produto_id` INT(11) NOT NULL,
  `produto_nome` VARCHAR(200) DEFAULT NULL,
  `qty` INT(11) NOT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_encomenda` (`encomenda_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `fk_itens_encomenda` FOREIGN KEY (`encomenda_id`) REFERENCES `encomendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: promocoes (Promoções de Produtos)
-- =============================================================================

DROP TABLE IF EXISTS `promocoes`;

CREATE TABLE `promocoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `produto_id` INT(11) NOT NULL,
  `desconto` DECIMAL(5,2) NOT NULL,
  `data_inicio` DATE NOT NULL,
  `data_fim` DATE NOT NULL,
  `ativo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_produto` (`produto_id`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_datas` (`data_inicio`, `data_fim`),
  CONSTRAINT `fk_promocoes_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- VIEWS ÚTEIS
-- =============================================================================

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
    p.categoria,
    COUNT(ei.id) as total_vendas,
    SUM(ei.qty) as total_unidades
FROM produtos p
LEFT JOIN encomenda_itens ei ON p.id = ei.produto_id
GROUP BY p.id, p.marca, p.modelo, p.slug, p.preco, p.imagem, p.categoria
ORDER BY total_vendas DESC, total_unidades DESC;

-- View: Estatísticas de encomendas
DROP VIEW IF EXISTS `v_stats_encomendas`;
CREATE VIEW `v_stats_encomendas` AS
SELECT 
    DATE(created_at) as data,
    COUNT(*) as total_encomendas,
    SUM(total) as receita_total,
    AVG(total) as ticket_medio,
    COUNT(CASE WHEN status = 'entregue' THEN 1 END) as entregues,
    COUNT(CASE WHEN status = 'cancelada' THEN 1 END) as canceladas
FROM encomendas
GROUP BY DATE(created_at)
ORDER BY data DESC;

-- View: Produtos com baixo stock
DROP VIEW IF EXISTS `v_produtos_baixo_stock`;
CREATE VIEW `v_produtos_baixo_stock` AS
SELECT 
    id,
    marca,
    modelo,
    categoria,
    stock,
    preco
FROM produtos
WHERE stock < 10 AND ativo = 1
ORDER BY stock ASC, categoria;

-- =============================================================================
-- TRIGGERS (Automação)
-- =============================================================================

-- Trigger: Atualizar stock após criar item de encomenda
DROP TRIGGER IF EXISTS `after_encomenda_item_insert`;
DELIMITER $$
CREATE TRIGGER `after_encomenda_item_insert` 
AFTER INSERT ON `encomenda_itens`
FOR EACH ROW
BEGIN
    -- Atualizar stock
    UPDATE produtos 
    SET stock = stock - NEW.qty 
    WHERE id = NEW.produto_id;
    
    -- Atualizar subtotal do item
    UPDATE encomenda_itens
    SET subtotal = NEW.qty * NEW.preco
    WHERE id = NEW.id;
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

-- Trigger: Auto-gerar SKU e nome se não fornecidos
DROP TRIGGER IF EXISTS `before_produto_insert`;
DELIMITER $$
CREATE TRIGGER `before_produto_insert` 
BEFORE INSERT ON `produtos`
FOR EACH ROW
BEGIN
    -- Gerar SKU se não fornecido
    IF NEW.sku IS NULL OR NEW.sku = '' THEN
        SET NEW.sku = CONCAT('SKU-', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM produtos), 6, '0'));
    END IF;
    
    -- Gerar nome se não fornecido
    IF NEW.nome IS NULL OR NEW.nome = '' THEN
        SET NEW.nome = CONCAT(NEW.marca, ' ', NEW.modelo);
    END IF;
    
    -- Calcular subtotal do preco_original
    IF NEW.preco_original IS NULL THEN
        SET NEW.preco_original = NEW.preco;
    END IF;
END$$
DELIMITER ;

-- =============================================================================
-- STORED PROCEDURES
-- =============================================================================

-- Procedure: Criar encomenda completa a partir do carrinho
DROP PROCEDURE IF EXISTS `sp_criar_encomenda`;
DELIMITER $$
CREATE PROCEDURE `sp_criar_encomenda`(
    IN p_user_id INT,
    IN p_morada TEXT,
    IN p_telefone VARCHAR(20),
    IN p_metodo_pagamento VARCHAR(50),
    OUT p_encomenda_id INT
)
BEGIN
    DECLARE v_total DECIMAL(10,2);
    DECLARE v_subtotal DECIMAL(10,2);
    DECLARE v_taxa_envio DECIMAL(10,2) DEFAULT 5.00;
    DECLARE v_iva DECIMAL(10,2);
    
    -- Calcular subtotal do carrinho
    SELECT SUM(p.preco * c.qty) INTO v_subtotal
    FROM carrinho c
    INNER JOIN produtos p ON c.produto_id = p.id
    WHERE c.user_id = p_user_id;
    
    -- Envio grátis acima de 50€
    IF v_subtotal >= 50 THEN
        SET v_taxa_envio = 0;
    END IF;
    
    -- Calcular IVA (23%)
    SET v_iva = (v_subtotal + v_taxa_envio) * 0.23;
    
    -- Calcular total
    SET v_total = v_subtotal + v_taxa_envio + v_iva;
    
    -- Criar encomenda
    INSERT INTO encomendas (user_id, total, subtotal, taxa_envio, iva, morada, telefone, metodo_pagamento, status)
    VALUES (p_user_id, v_total, v_subtotal, v_taxa_envio, v_iva, p_morada, p_telefone, p_metodo_pagamento, 'pendente');
    
    SET p_encomenda_id = LAST_INSERT_ID();
    
    -- Copiar itens do carrinho para encomenda
    INSERT INTO encomenda_itens (encomenda_id, produto_id, produto_nome, qty, preco, subtotal)
    SELECT 
        p_encomenda_id, 
        c.produto_id, 
        CONCAT(p.marca, ' ', p.modelo),
        c.qty, 
        p.preco,
        p.preco * c.qty
    FROM carrinho c
    INNER JOIN produtos p ON c.produto_id = p.id
    WHERE c.user_id = p_user_id;
    
    -- Limpar carrinho
    DELETE FROM carrinho WHERE user_id = p_user_id;
END$$
DELIMITER ;

-- Procedure: Pesquisa de produtos
DROP PROCEDURE IF EXISTS `sp_pesquisar_produtos`;
DELIMITER $$
CREATE PROCEDURE `sp_pesquisar_produtos`(
    IN p_termo VARCHAR(255),
    IN p_categoria VARCHAR(100),
    IN p_limit INT
)
BEGIN
    SELECT 
        id,
        marca,
        modelo,
        nome,
        slug,
        preco,
        preco_original,
        imagem,
        categoria,
        stock,
        CASE
            WHEN marca LIKE CONCAT(p_termo, '%') THEN 1
            WHEN modelo LIKE CONCAT(p_termo, '%') THEN 2
            WHEN nome LIKE CONCAT(p_termo, '%') THEN 3
            WHEN marca LIKE CONCAT('%', p_termo, '%') THEN 4
            WHEN modelo LIKE CONCAT('%', p_termo, '%') THEN 5
            ELSE 6
        END as relevancia
    FROM produtos
    WHERE 
        ativo = 1 
        AND (p_categoria IS NULL OR categoria = p_categoria)
        AND (
            marca LIKE CONCAT('%', p_termo, '%') OR
            modelo LIKE CONCAT('%', p_termo, '%') OR
            nome LIKE CONCAT('%', p_termo, '%') OR
            descricao LIKE CONCAT('%', p_termo, '%')
        )
    ORDER BY relevancia, preco
    LIMIT p_limit;
END$$
DELIMITER ;

-- =============================================================================
-- ÍNDICES DE PERFORMANCE ADICIONAIS
-- =============================================================================

-- Já foram criados nos CREATE TABLE acima, mas listados aqui para referência:
-- produtos: idx_categoria_ativo, idx_marca_categoria, idx_categoria_preco
-- encomendas: idx_user, idx_status, idx_created
-- favoritos: unique_favorito, idx_user, idx_produto
-- carrinho: unique_cart_item, idx_user, idx_produto

-- =============================================================================
-- OTIMIZAÇÃO FINAL
-- =============================================================================

-- Analisar tabelas para melhor performance
ANALYZE TABLE users, categories, brands, produtos, favoritos, comparacao, carrinho, encomendas, encomenda_itens, promocoes;

-- Otimizar todas as tabelas
OPTIMIZE TABLE users, categories, brands, produtos, favoritos, comparacao, carrinho, encomendas, encomenda_itens, promocoes;

-- =============================================================================
-- ESTATÍSTICAS FINAIS
-- =============================================================================

SELECT '✅ BASE DE DADOS CRIADA COM SUCESSO!' AS Status;

SELECT 
    'Tabelas Criadas' AS Info,
    COUNT(*) AS Total
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'gomestech' AND TABLE_TYPE = 'BASE TABLE';

SELECT 
    'Views Criadas' AS Info,
    COUNT(*) AS Total
FROM information_schema.VIEWS 
WHERE TABLE_SCHEMA = 'gomestech';

SELECT 
    'Triggers Criados' AS Info,
    COUNT(*) AS Total
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'gomestech';

SELECT 
    'Stored Procedures' AS Info,
    COUNT(*) AS Total
FROM information_schema.ROUTINES 
WHERE ROUTINE_SCHEMA = 'gomestech' AND ROUTINE_TYPE = 'PROCEDURE';

-- =============================================================================
-- INSTRUÇÕES DE USO
-- =============================================================================

-- 1. Importar este ficheiro no phpMyAdmin
-- 2. Executar: database/importar_catalogo_json.php (para importar produtos)
-- 3. Aceder a: http://localhost/gomestech/
-- 4. Login admin: admin@gomestech.pt / admin123
-- 
-- ESTRUTURA CRIADA:
-- ✓ 10 Tabelas principais
-- ✓ 13 Categorias
-- ✓ 42 Marcas
-- ✓ Sistema de favoritos
-- ✓ Sistema de comparação
-- ✓ Carrinho de compras
-- ✓ Sistema de encomendas completo
-- ✓ Sistema de promoções
-- ✓ 3 Views úteis
-- ✓ 3 Triggers automáticos
-- ✓ 2 Stored Procedures
-- ✓ Índices otimizados
-- ✓ Foreign keys configuradas
-- ✓ Campos de timestamp automáticos
-- 
-- =============================================================================

SET FOREIGN_KEY_CHECKS=1;
