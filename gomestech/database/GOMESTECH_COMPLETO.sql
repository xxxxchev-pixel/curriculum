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

-- Inserir produtos de exemplo de todas as categorias
INSERT INTO `produtos` (`sku`, `marca`, `modelo`, `categoria`, `preco`, `preco_original`, `stock`, `imagem`, `destaque`, `novidade`, `produto_dia`, `descricao`) VALUES
-- Smartphones
('SM-001', 'Apple', 'iPhone 15 Pro 256GB', 'Smartphones', 1299.99, 1499.99, 50, 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=500', 1, 1, 0, 'iPhone 15 Pro com chip A17 Pro, câmara de 48MP e design em titânio.'),
('SM-002', 'Samsung', 'Galaxy S24 Ultra 512GB', 'Smartphones', 1399.99, 1599.99, 45, 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=500', 1, 1, 1, 'Galaxy S24 Ultra com S Pen, câmara de 200MP e ecrã Dynamic AMOLED 2X.'),
('SM-003', 'Google', 'Pixel 8 Pro 256GB', 'Smartphones', 999.99, 1199.99, 30, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=500', 1, 0, 0, 'Google Pixel 8 Pro com Tensor G3 e melhor IA fotográfica do mercado.'),
('SM-004', 'OnePlus', '12 Pro 512GB', 'Smartphones', 899.99, 1099.99, 25, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500', 0, 1, 0, 'OnePlus 12 Pro com carregamento rápido 100W e Snapdragon 8 Gen 3.'),
('SM-005', 'Xiaomi', '14 Ultra 512GB', 'Smartphones', 1199.99, 1399.99, 20, 'https://images.unsplash.com/photo-1592286927505-2fd3c4e5e3c4?w=500', 1, 1, 0, 'Xiaomi 14 Ultra com câmaras Leica e ecrã de 120Hz.'),

-- Laptops
('LP-001', 'Apple', 'MacBook Pro 16" M3 Max', 'Laptops', 3499.99, 3999.99, 15, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500', 1, 1, 0, 'MacBook Pro com chip M3 Max, 36GB RAM e ecrã Liquid Retina XDR.'),
('LP-002', 'Dell', 'XPS 15 9530 i9', 'Laptops', 2299.99, 2699.99, 20, 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=500', 1, 0, 0, 'Dell XPS 15 com Intel Core i9, RTX 4060 e ecrã 4K OLED.'),
('LP-003', 'Lenovo', 'ThinkPad X1 Carbon Gen 11', 'Laptops', 1799.99, 2099.99, 25, 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500', 0, 0, 0, 'Lenovo ThinkPad ultra-portátil com Intel Core i7 e 32GB RAM.'),
('LP-004', 'HP', 'Spectre x360 14"', 'Laptops', 1599.99, 1899.99, 18, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500', 0, 1, 0, 'HP Spectre conversível com ecrã táctil OLED e Intel Evo.'),
('LP-005', 'Asus', 'ROG Zephyrus G16', 'Laptops', 2499.99, 2899.99, 12, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500', 1, 1, 0, 'Asus ROG gaming laptop com RTX 4080 e ecrã Mini LED.'),

-- Tablets
('TB-001', 'Apple', 'iPad Pro 12.9" M2 512GB', 'Tablets', 1499.99, 1699.99, 30, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=500', 1, 0, 0, 'iPad Pro com chip M2, ecrã Liquid Retina XDR e Apple Pencil.'),
('TB-002', 'Samsung', 'Galaxy Tab S9 Ultra 512GB', 'Tablets', 1299.99, 1499.99, 25, 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=500', 1, 1, 0, 'Galaxy Tab S9 Ultra com ecrã AMOLED de 14.6" e S Pen incluída.'),
('TB-003', 'Lenovo', 'Tab P12 Pro', 'Tablets', 699.99, 899.99, 20, 'https://images.unsplash.com/photo-1585789575762-9f035d46f8ab?w=500', 0, 0, 0, 'Lenovo Tab P12 Pro com ecrã AMOLED e caneta opcional.'),
('TB-004', 'Microsoft', 'Surface Pro 9', 'Tablets', 1199.99, 1399.99, 15, 'https://images.unsplash.com/photo-1587033411391-5d9e51cce126?w=500', 0, 1, 0, 'Microsoft Surface Pro 9 com Windows 11 e Type Cover.'),

-- Wearables
('WR-001', 'Apple', 'Watch Series 9 45mm GPS', 'Wearables', 479.99, 549.99, 40, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=500', 1, 1, 0, 'Apple Watch Series 9 com sensor de temperatura e detecção de quedas.'),
('WR-002', 'Samsung', 'Galaxy Watch 6 Classic 47mm', 'Wearables', 399.99, 479.99, 35, 'https://images.unsplash.com/photo-1617625802912-cde586faf331?w=500', 1, 0, 0, 'Samsung Galaxy Watch 6 Classic com moldura rotativa e sensor BioActive.'),
('WR-003', 'Garmin', 'Fenix 7X Pro Solar', 'Wearables', 899.99, 1099.99, 15, 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=500', 1, 0, 0, 'Garmin Fenix 7X Pro com GPS multi-banda e carregamento solar.'),
('WR-004', 'Google', 'Pixel Watch 2', 'Wearables', 349.99, 399.99, 25, 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500', 0, 1, 0, 'Google Pixel Watch 2 com Fitbit integrado e Wear OS.'),

-- TVs / Televisores
('TV-001', 'Samsung', 'Neo QLED 65" QN95C', 'TVs', 2499.99, 2999.99, 10, 'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=500', 1, 1, 0, 'Samsung Neo QLED 4K com Quantum Matrix e Object Tracking Sound+.'),
('TV-002', 'LG', 'OLED evo 65" C3', 'TVs', 2299.99, 2799.99, 12, 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=500', 1, 1, 0, 'LG OLED evo com processador α9 Gen6 AI e Dolby Vision IQ.'),
('TV-003', 'Sony', 'Bravia XR 65" A95K', 'TVs', 2999.99, 3499.99, 8, 'https://images.unsplash.com/photo-1593359863503-f598d-27de6f?w=500', 1, 0, 0, 'Sony Bravia QD-OLED com XR Triluminos Max e Acoustic Surface Audio+.'),
('TV-004', 'Philips', 'OLED 55" 808', 'TVs', 1799.99, 2199.99, 15, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500', 0, 0, 0, 'Philips OLED com Ambilight 4 lados e P5 AI Dual Engine.'),

-- Audio
('AU-001', 'Sony', 'WH-1000XM5', 'Audio', 399.99, 449.99, 50, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500', 1, 1, 0, 'Sony WH-1000XM5 com cancelamento de ruído líder da indústria.'),
('AU-002', 'Bose', 'QuietComfort Ultra', 'Audio', 449.99, 499.99, 40, 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=500', 1, 0, 0, 'Bose QuietComfort Ultra com áudio espacial e ANC premium.'),
('AU-003', 'Apple', 'AirPods Max', 'Audio', 579.99, 629.99, 30, 'https://images.unsplash.com/photo-1625644312452-6e8c6c8ed5b8?w=500', 1, 0, 0, 'Apple AirPods Max com áudio espacial e design premium em alumínio.'),
('AU-004', 'JBL', 'Charge 5', 'Audio', 179.99, 219.99, 60, 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500', 0, 0, 0, 'JBL Charge 5 coluna portátil com IP67 e powerbank integrado.'),
('AU-005', 'Marshall', 'Emberton II', 'Audio', 169.99, 199.99, 45, 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=500', 0, 1, 0, 'Marshall Emberton II com som 360° e 30 horas de bateria.'),

-- Auriculares
('AUR-001', 'Apple', 'AirPods Pro 2ª Gen', 'Auriculares', 279.99, 299.99, 70, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500', 1, 1, 0, 'AirPods Pro com chip H2, ANC adaptativo e áudio espacial.'),
('AUR-002', 'Samsung', 'Galaxy Buds2 Pro', 'Auriculares', 229.99, 259.99, 55, 'https://images.unsplash.com/photo-1606841837239-c5a1a4a07af7?w=500', 1, 0, 0, 'Galaxy Buds2 Pro com ANC inteligente e áudio 360° com Dolby Atmos.'),
('AUR-003', 'Sony', 'WF-1000XM5', 'Auriculares', 299.99, 329.99, 40, 'https://images.unsplash.com/photo-1613040809024-b4ef7ba99bc3?w=500', 1, 1, 0, 'Sony WF-1000XM5 com melhor ANC e LDAC para áudio Hi-Res.'),
('AUR-004', 'Bose', 'QuietComfort Earbuds II', 'Auriculares', 279.99, 319.99, 35, 'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=500', 0, 0, 0, 'Bose QC Earbuds II com ANC personalizado e ajuste perfeito.'),

-- Headphones
('HP-001', 'Sony', 'WH-1000XM5', 'Headphones', 399.99, 449.99, 45, 'https://images.unsplash.com/photo-1545127398-14699f92334b?w=500', 1, 1, 0, 'Sony WH-1000XM5 headphones premium com 30h de bateria.'),
('HP-002', 'Bose', '700', 'Headphones', 379.99, 429.99, 35, 'https://images.unsplash.com/photo-1577174881658-0f30157f72c4?w=500', 1, 0, 0, 'Bose 700 com 11 níveis de cancelamento de ruído.'),
('HP-003', 'Apple', 'AirPods Max', 'Headphones', 579.99, 629.99, 25, 'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=500', 1, 0, 0, 'AirPods Max com design premium e áudio espacial.'),
('HP-004', 'Sennheiser', 'Momentum 4', 'Headphones', 349.99, 399.99, 30, 'https://images.unsplash.com/photo-1599669454699-248893623440?w=500', 0, 1, 0, 'Sennheiser Momentum 4 com 60h de bateria e som audiófilo.'),

-- Consolas
('CO-001', 'Sony', 'PlayStation 5 Slim', 'Consolas', 499.99, 549.99, 20, 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?w=500', 1, 1, 0, 'PlayStation 5 Slim com 1TB SSD e DualSense.'),
('CO-002', 'Microsoft', 'Xbox Series X', 'Consolas', 499.99, 549.99, 18, 'https://images.unsplash.com/photo-1621259182978-fbf93132d53d?w=500', 1, 0, 0, 'Xbox Series X com 1TB e Game Pass.'),
('CO-003', 'Nintendo', 'Switch OLED', 'Consolas', 349.99, 379.99, 35, 'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=500', 1, 1, 0, 'Nintendo Switch OLED com ecrã de 7".'),

-- Frigoríficos
('FR-001', 'Samsung', 'Bespoke RB38C7B5D22', 'Frigoríficos', 1299.99, 1499.99, 8, 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=500', 1, 0, 0, 'Samsung Bespoke combinado No Frost com Twin Cooling Plus.'),
('FR-002', 'LG', 'GMX945MC9F', 'Frigoríficos', 1799.99, 2099.99, 6, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=500', 1, 1, 0, 'LG frigorífico americano InstaView Door-in-Door com dispensador.'),
('FR-003', 'Bosch', 'KGN49AICP', 'Frigoríficos', 1099.99, 1299.99, 10, 'https://images.unsplash.com/photo-1556909172-54557c7e4fb7?w=500', 0, 0, 0, 'Bosch combinado No Frost com VitaFresh Pro.'),

-- Máquinas de Lavar
('ML-001', 'Samsung', 'WW90T554DTX', 'Máquinas de Lavar', 699.99, 849.99, 12, 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=500', 1, 0, 0, 'Samsung máquina de lavar 9kg AddWash com AI Control.'),
('ML-002', 'LG', 'F4WV909P2E', 'Máquinas de Lavar', 799.99, 949.99, 10, 'https://images.unsplash.com/photo-1604335399105-a0c585fd81a1?w=500', 1, 1, 0, 'LG máquina de lavar 9kg com AI DD e Steam.'),
('ML-003', 'Bosch', 'WAX32EH0ES', 'Máquinas de Lavar', 749.99, 899.99, 15, 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?w=500', 0, 0, 0, 'Bosch máquina de lavar 10kg com i-Dos.'),

-- Micro-ondas
('MW-001', 'Samsung', 'MG30T5018CG', 'Micro-ondas', 199.99, 249.99, 25, 'https://images.unsplash.com/photo-1585659722983-3a675dabf23d?w=500', 0, 0, 0, 'Samsung micro-ondas com grill 30L e Smart Moisture Sensor.'),
('MW-002', 'Panasonic', 'NN-SD28HSGTG', 'Micro-ondas', 179.99, 229.99, 30, 'https://images.unsplash.com/photo-1574269909862-7e1d70bb8078?w=500', 0, 1, 0, 'Panasonic micro-ondas 23L com Inverter Technology.'),
('MW-003', 'LG', 'NeoChef MH7265DPS', 'Micro-ondas', 219.99, 269.99, 20, 'https://images.unsplash.com/photo-1612528443702-f6741f70a049?w=500', 1, 0, 0, 'LG micro-ondas 32L com Smart Inverter e grill.'),

-- Aspiradores
('AS-001', 'Dyson', 'V15 Detect Absolute', 'Aspiradores', 699.99, 799.99, 15, 'https://images.unsplash.com/photo-1558317374-067fb5f30001?w=500', 1, 1, 0, 'Dyson V15 Detect com laser para deteção de pó invisível.'),
('AS-002', 'iRobot', 'Roomba j7+', 'Aspiradores', 799.99, 949.99, 12, 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=500', 1, 0, 0, 'iRobot Roomba j7+ com esvaziamento automático e PrecisionVision.'),
('AS-003', 'Rowenta', 'X-Force Flex 11.60', 'Aspiradores', 449.99, 549.99, 20, 'https://images.unsplash.com/photo-1625772452859-1c03d5bf1137?w=500', 0, 1, 0, 'Rowenta sem fios flexível com 45min de autonomia.'),

-- Ar Condicionado
('AC-001', 'Daikin', 'FTXM35R Perfera', 'Ar Condicionado', 899.99, 1099.99, 8, 'https://images.unsplash.com/photo-1631545806609-c8f0e1f97d7e?w=500', 1, 0, 0, 'Daikin Perfera split 12000 BTU com Filtro Flash Streamer.'),
('AC-002', 'Mitsubishi', 'MSZ-LN35VGW', 'Ar Condicionado', 1099.99, 1299.99, 6, 'https://images.unsplash.com/photo-1634554939598-08d6025e99b8?w=500', 1, 1, 0, 'Mitsubishi split 12000 BTU com Wi-Fi e tecnologia 3D i-see.'),
('AC-003', 'LG', 'PC12SQ Artcool', 'Ar Condicionado', 799.99, 999.99, 10, 'https://images.unsplash.com/photo-1602407294553-6ac9170b3ed0?w=500', 0, 0, 0, 'LG Artcool split 12000 BTU com design espelho.'),
('AC-004', 'Fujitsu', 'ASYG12KPCA', 'Ar Condicionado', 749.99, 899.99, 12, 'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=500', 0, 0, 0, 'Fujitsu split 12000 BTU Eco com classificação A++.'),
('AC-005', 'Samsung', 'AR12BXWXCWKNEU WindFree', 'Ar Condicionado', 849.99, 1049.99, 9, 'https://images.unsplash.com/photo-1614252369475-531eba835eb1?w=500', 1, 1, 0, 'Samsung WindFree split 12000 BTU com tecnologia sem correntes de ar.'),

-- Máquinas de Café
('MC-001', 'De\'Longhi', 'Magnifica S ECAM22.110.B', 'Máquinas de Café', 449.99, 549.99, 20, 'https://images.unsplash.com/photo-1517668808822-9ebb02f2a0e6?w=500', 1, 0, 0, 'De\'Longhi automática com moinho integrado e Cappuccino System.'),
('MC-002', 'Nespresso', 'Vertuo Next', 'Máquinas de Café', 169.99, 199.99, 35, 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=500', 0, 1, 0, 'Nespresso Vertuo Next com sistema Centrifusion.'),
('MC-003', 'Sage', 'Barista Express SES875', 'Máquinas de Café', 699.99, 799.99, 12, 'https://images.unsplash.com/photo-1556742044-3c52d6e88c62?w=500', 1, 1, 0, 'Sage Barista Express com moinho cónico integrado.'),
('MC-004', 'Krups', 'EA8108 Roma', 'Máquinas de Café', 399.99, 479.99, 18, 'https://images.unsplash.com/photo-1580933073521-dc49ac0d4e6a?w=500', 0, 0, 0, 'Krups Roma automática compacta com vaporizador.');

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
