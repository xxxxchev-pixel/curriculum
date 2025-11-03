-- =============================================================================
-- GOMESTECH - BASE DE DADOS COMPLETA COM 80 PRODUTOS
-- Todos os produtos organizados por categorias e marcas
-- Data: 2024
-- =============================================================================

-- Eliminar base de dados antiga (se existir)
DROP DATABASE IF EXISTS gomestech;

-- Criar nova base de dados
CREATE DATABASE gomestech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gomestech;

-- =============================================================================
-- ESTRUTURA DAS TABELAS
-- =============================================================================

-- Tabela de Utilizadores
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    morada TEXT,
    nif VARCHAR(9),
    codigo_postal VARCHAR(8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_nif (nif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Categorias
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50),
    parent_id INT DEFAULT NULL,
    display_order INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Marcas
CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    logo VARCHAR(500),
    display_order INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Relação Categoria-Marca
CREATE TABLE category_brand (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    brand_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_brand (category_id, brand_id),
    INDEX idx_category (category_id),
    INDEX idx_brand (brand_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(50) NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(150) NOT NULL,
    nome VARCHAR(200),
        slug VARCHAR(250) UNIQUE,
    preco DECIMAL(10,2) NOT NULL,
    preco_original DECIMAL(10,2),
    loja VARCHAR(100),
    imagem VARCHAR(500),
    descricao TEXT,
    stock INT DEFAULT 100,
    destaque BOOLEAN DEFAULT FALSE,
    novidade BOOLEAN DEFAULT FALSE,
    category_id INT,
    brand_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    INDEX idx_categoria (categoria),
    INDEX idx_marca (marca),
    INDEX idx_preco (preco),
    INDEX idx_destaque (destaque),
    INDEX idx_novidade (novidade),
    INDEX idx_category (category_id),
    INDEX idx_brand (brand_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Encomendas
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    morada TEXT NOT NULL,
    cidade VARCHAR(100),
    codigo_postal VARCHAR(20) NOT NULL,
    nif VARCHAR(9),
    metodo_pagamento VARCHAR(50) NOT NULL,
    morada_entrega TEXT,
    subtotal DECIMAL(10,2) NOT NULL,
    taxa_envio DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    iva DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pendente',
    notas TEXT,
    tracking_code VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Itens da Encomenda
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    produto_id INT NOT NULL,
    produto_nome VARCHAR(200) NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Carrinho
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(100),
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_produto (user_id, produto_id),
    UNIQUE KEY unique_session_produto (session_id, produto_id),
    INDEX idx_user (user_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Avaliações
CREATE TABLE product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    titulo VARCHAR(200),
    comentario TEXT,
    verified_purchase BOOLEAN DEFAULT FALSE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_produto (produto_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- DADOS: 13 CATEGORIAS
-- =============================================================================

INSERT INTO categories (id, name, slug, icon, parent_id, display_order, active) VALUES
(1, 'Smartphones', 'smartphones', '📱', NULL, 1, 1),
(2, 'Laptops', 'laptops', 'fa-laptop', NULL, 2, 1),
(3, 'Tablets', 'tablets', 'fa-tablet-alt', NULL, 3, 1),
(4, 'Wearables', 'wearables', 'fa-watch', NULL, 4, 1),
(5, 'TVs', 'tvs', 'fa-tv', NULL, 5, 1),
(6, 'Audio', 'audio', 'fa-headphones', NULL, 6, 1),
(7, 'Consolas', 'consolas', 'fa-gamepad', NULL, 7, 1),
(8, 'Frigoríficos', 'frigorificos', 'fa-refrigerator', NULL, 8, 1),
(9, 'Máquinas de Lavar', 'maquinas-lavar', 'fa-washer', NULL, 9, 1),
(10, 'Micro-ondas', 'micro-ondas', 'fa-microwave', NULL, 10, 1),
(11, 'Aspiradores', 'aspiradores', 'fa-vacuum', NULL, 11, 1),
(12, 'Ar Condicionado', 'ar-condicionado', 'fa-fan', NULL, 12, 1),
(13, 'Máquinas de Café', 'maquinas-cafe', 'fa-coffee', NULL, 13, 1);

-- =============================================================================
-- DADOS: 42 MARCAS
-- =============================================================================

INSERT INTO brands (id, name, slug, logo, display_order, active) VALUES
(1, 'Apple', 'apple', '', 1, 1),
(2, 'Samsung', 'samsung', '', 2, 1),
(3, 'Google', 'google', '', 3, 1),
(4, 'OnePlus', 'oneplus', '', 4, 1),
(5, 'Dell', 'dell', '', 5, 1),
(6, 'Lenovo', 'lenovo', '', 6, 1),
(7, 'HP', 'hp', '', 7, 1),
(8, 'Asus', 'asus', '', 8, 1),
(9, 'MSI', 'msi', '', 9, 1),
(10, 'Sony', 'sony', '', 10, 1),
(11, 'Microsoft', 'microsoft', '', 11, 1),
(12, 'Nintendo', 'nintendo', '', 12, 1),
(13, 'LG', 'lg', '', 13, 1),
(14, 'Philips', 'philips', '', 14, 1),
(15, 'TP-Link', 'tp-link', '', 15, 1),
(16, 'Xiaomi', 'xiaomi', '', 16, 1),
(17, 'Motorola', 'motorola', '', 17, 1),
(18, 'Realme', 'realme', '', 18, 1),
(19, 'Garmin', 'garmin', '', 19, 1),
(20, 'Bose', 'bose', '', 20, 1),
(21, 'JBL', 'jbl', '', 21, 1),
(22, 'Sonos', 'sonos', '', 22, 1),
(23, 'Marshall', 'marshall', '', 23, 1),
(24, 'Harman Kardon', 'harman-kardon', '', 24, 1),
(25, 'Amazon', 'amazon', '', 25, 1),
(26, 'Bosch', 'bosch', '', 26, 1),
(27, 'Siemens', 'siemens', '', 27, 1),
(28, 'Whirlpool', 'whirlpool', '', 28, 1),
(29, 'Beko', 'beko', '', 29, 1),
(30, 'Indesit', 'indesit', '', 30, 1),
(31, 'Teka', 'teka', '', 31, 1),
(32, 'Panasonic', 'panasonic', '', 32, 1),
(33, 'Dyson', 'dyson', '', 33, 1),
(34, 'Rowenta', 'rowenta', '', 34, 1),
(35, 'iRobot', 'irobot', '', 35, 1),
(36, 'Daikin', 'daikin', '', 36, 1),
(37, 'Mitsubishi', 'mitsubishi', '', 37, 1),
(38, 'Fujitsu', 'fujitsu', '', 38, 1),
(39, 'De\'Longhi', 'delonghi', '', 39, 1),
(40, 'Nespresso', 'nespresso', '', 40, 1),
(41, 'Sage', 'sage', '', 41, 1),
(42, 'Krups', 'krups', '', 42, 1);

-- =============================================================================
-- DADOS: 80 PRODUTOS ORGANIZADOS POR CATEGORIA
-- =============================================================================


INSERT INTO produtos (categoria, marca, modelo, nome, slug, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Smartphones', 'Apple', 'iPhone 15 Pro', 'Apple iPhone 15 Pro', 'smartphones/apple/iphone-15-pro', 1199.00, 1299.00, 'Amazon', 'https://m.media-amazon.com/images/I/81SigpJN1KL._AC_SX679_.jpg', 'iPhone 15 Pro com chip A17 Pro, câmara de 48MP e ecrã Super Retina XDR de 6.1 polegadas.', 100, 1, 1, 1, 1),
('Smartphones', 'Apple', 'iPhone 15', 'Apple iPhone 15', 'smartphones/apple/iphone-15', 899.00, 979.00, 'Amazon', 'https://m.media-amazon.com/images/I/71xb2xkN5qL._AC_SX679_.jpg', 'iPhone 15 com chip A16 Bionic e sistema de câmara dupla de 48MP.', 100, 1, 1, 1, 1),
('Smartphones', 'Samsung', 'Galaxy S24 Ultra', 'Samsung Galaxy S24 Ultra', 'smartphones/samsung/galaxy-s24-ultra', 1299.00, 1399.00, 'Amazon', 'https://m.media-amazon.com/images/I/71RxOftSvJL._AC_SX679_.jpg', 'Galaxy S24 Ultra com S Pen integrada, ecrã AMOLED de 6.8 polegadas e câmara de 200MP.', 100, 1, 1, 1, 2),
('Smartphones', 'Samsung', 'Galaxy S24+', 'Samsung Galaxy S24+', 'smartphones/samsung/galaxy-s24', 999.00, 1099.00, 'Amazon', 'https://m.media-amazon.com/images/I/71CUOBmLQuL._AC_SX679_.jpg', 'Galaxy S24+ com Snapdragon 8 Gen 3 e bateria de 4900mAh.', 100, 0, 1, 1, 2),
('Smartphones', 'Google', 'Pixel 8 Pro', 'Google Pixel 8 Pro', 'smartphones/google/pixel-8-pro', 899.00, 999.00, 'Amazon', 'https://m.media-amazon.com/images/I/71KMSEPPnkL._AC_SX679_.jpg', 'Pixel 8 Pro com Google Tensor G3, câmara tripla de 50MP e Android puro.', 100, 1, 1, 1, 3),
('Smartphones', 'OnePlus', 'OnePlus 12', 'OnePlus 12', 'smartphones/oneplus/oneplus-12', 799.00, 899.00, 'Amazon', 'https://m.media-amazon.com/images/I/61o7vczRU0L._AC_SX679_.jpg', 'OnePlus 12 com Snapdragon 8 Gen 3, carregamento rápido de 100W e ecrã AMOLED de 120Hz.', 100, 0, 1, 1, 4);


INSERT INTO produtos (categoria, marca, modelo, nome, slug, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Laptops', 'Apple', 'MacBook Air 13" M4', 'Apple MacBook Air 13" M4', 'laptops/apple/macbook-air-13-m4', 1299.00, 1399.00, 'Amazon', 'https://m.media-amazon.com/images/I/71TPda7cwUL._AC_SX679_.jpg', 'MacBook Air com chip M4, 16GB RAM, 512GB SSD e ecrã Retina de 13.6 polegadas.', 100, 1, 1, 2, 1),
('Laptops', 'Apple', 'MacBook Pro 14" M4', 'Apple MacBook Pro 14" M4', 'laptops/apple/macbook-pro-14-m4', 1999.00, 2199.00, 'Amazon', 'https://m.media-amazon.com/images/I/61fXK4LBG6L._AC_SX679_.jpg', 'MacBook Pro 14" com chip M4 Pro, 18GB RAM e ecrã Liquid Retina XDR.', 100, 1, 1, 2, 1),
('Laptops', 'Dell', 'XPS 13', 'Dell XPS 13', 'laptops/dell/xps-13', 1099.00, 1299.00, 'Amazon', 'https://m.media-amazon.com/images/I/71w7kKX8vJL._AC_SX679_.jpg', 'Dell XPS 13 com Intel Core Ultra 7, 16GB RAM e ecrã InfinityEdge de 13.4 polegadas.', 100, 1, 0, 2, 5),
('Laptops', 'Lenovo', 'ThinkPad X1 Carbon', 'Lenovo ThinkPad X1 Carbon', 'laptops/lenovo/thinkpad-x1-carbon', 1399.00, 1599.00, 'Amazon', 'https://m.media-amazon.com/images/I/51W7yLG7EJL._AC_SX679_.jpg', 'ThinkPad X1 Carbon Gen 12 com Intel Core Ultra 7, 32GB RAM e certificação militar MIL-STD-810H.', 100, 1, 0, 2, 6),
('Laptops', 'HP', 'Spectre x360 14', 'HP Spectre x360 14', 'laptops/hp/spectre-x360-14', 1299.00, 1499.00, 'Amazon', 'https://m.media-amazon.com/images/I/71KdUVCe9sL._AC_SX679_.jpg', 'HP Spectre x360 conversível com ecrã OLED 2.8K, Intel Core Ultra 7 e design 2-em-1.', 100, 0, 0, 2, 7);


INSERT INTO produtos (categoria, marca, modelo, nome, slug, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Tablets', 'Apple', 'iPad Pro 12.9" M2', 'Apple iPad Pro 12.9" M2', 'tablets/apple/ipad-pro-12-9-m2', 1299.00, 1449.00, 'Amazon', 'https://m.media-amazon.com/images/I/81Y3d9HvpGL._AC_SX679_.jpg', 'iPad Pro com chip M2, ecrã Liquid Retina XDR de 12.9 polegadas e suporte para Apple Pencil 2.', 100, 1, 0, 3, 1),
('Tablets', 'Apple', 'iPad Air 11"', 'Apple iPad Air 11"', 'tablets/apple/ipad-air-11', 699.00, 799.00, 'Amazon', 'https://m.media-amazon.com/images/I/61eWt3hjBWL._AC_SX679_.jpg', 'iPad Air com chip M1 e ecrã Liquid Retina de 11 polegadas.', 100, 0, 1, 3, 1),
('Tablets', 'Samsung', 'Galaxy Tab S9 Ultra', 'Samsung Galaxy Tab S9 Ultra', 'tablets/samsung/galaxy-tab-s9-ultra', 1199.00, 1349.00, 'Amazon', 'https://m.media-amazon.com/images/I/71swLf7ew7L._AC_SX679_.jpg', 'Galaxy Tab S9 Ultra com ecrã AMOLED de 14.6 polegadas, S Pen incluída e resistência à água IP68.', 100, 1, 0, 3, 2),
('Tablets', 'Samsung', 'Galaxy Tab S9', 'Samsung Galaxy Tab S9', 'tablets/samsung/galaxy-tab-s9', 799.00, 899.00, 'Amazon', 'https://m.media-amazon.com/images/I/61T1Z2qW5vL._AC_SX679_.jpg', 'Galaxy Tab S9 com ecrã AMOLED de 11 polegadas e Snapdragon 8 Gen 2.', 100, 0, 0, 3, 2),
('Tablets', 'Microsoft', 'Surface Pro 9', 'Microsoft Surface Pro 9', 'tablets/microsoft/surface-pro-9', 999.00, 1199.00, 'Amazon', 'https://m.media-amazon.com/images/I/71lVMZjz4WL._AC_SX679_.jpg', 'Surface Pro 9 com Intel Core i7, 16GB RAM e ecrã PixelSense de 13 polegadas.', 100, 1, 0, 3, 11);


INSERT INTO produtos (categoria, marca, modelo, nome, slug, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Wearables', 'Apple', 'Apple Watch Series 9', 'Apple Watch Series 9', 'wearables/apple/apple-watch-series-9', 429.00, 479.00, 'Amazon', 'https://m.media-amazon.com/images/I/71JETy0XhPL._AC_SX679_.jpg', 'Apple Watch Series 9 com chip S9, ecrã sempre ativo e sensores de saúde avançados.', 100, 1, 1, 4, 1),
('Wearables', 'Apple', 'Apple Watch Ultra 2', 'Apple Watch Ultra 2', 'wearables/apple/apple-watch-ultra-2', 799.00, 899.00, 'Amazon', 'https://m.media-amazon.com/images/I/81E1C1-umuL._AC_SX679_.jpg', 'Apple Watch Ultra 2 para desportos extremos, com caixa de titânio e autonomia até 36h.', 100, 1, 1, 4, 1),
('Wearables', 'Samsung', 'Galaxy Watch6 Classic', 'Samsung Galaxy Watch6 Classic', 'wearables/samsung/galaxy-watch6-classic', 399.00, 449.00, 'Amazon', 'https://m.media-amazon.com/images/I/61E-HlX7xbL._AC_SX679_.jpg', 'Galaxy Watch6 Classic com anel rotativo, monitorização de saúde e design premium.', 100, 1, 1, 4, 2),
('Wearables', 'Samsung', 'Galaxy Watch6', 'Samsung Galaxy Watch6', 'wearables/samsung/galaxy-watch6', 299.00, 349.00, 'Amazon', 'https://m.media-amazon.com/images/I/612hd2x3fwL._AC_SX679_.jpg', 'Galaxy Watch6 com ecrã AMOLED e funcionalidades de bem-estar.', 100, 0, 1, 4, 2),
('Wearables', 'Google', 'Pixel Watch 2', 'Google Pixel Watch 2', 'wearables/google/pixel-watch-2', 349.00, 399.00, 'Amazon', 'https://m.media-amazon.com/images/I/61zPIKmOW0L._AC_SX679_.jpg', 'Pixel Watch 2 com Wear OS, sensores Fitbit e design circular elegante.', 100, 1, 1, 4, 3),
('Wearables', 'Garmin', 'Forerunner 965', 'Garmin Forerunner 965', 'wearables/garmin/forerunner-965', 599.00, 649.00, 'Amazon', 'https://m.media-amazon.com/images/I/71dVzHP4gYL._AC_SX679_.jpg', 'Garmin Forerunner 965 com ecrã AMOLED, GPS multi-banda e métricas avançadas de corrida.', 100, 1, 0, 4, 19),
('Wearables', 'Garmin', 'Fenix 7X Sapphire Solar', 'Garmin Fenix 7X Sapphire Solar', 'wearables/garmin/fenix-7x-sapphire-solar', 899.00, 999.00, 'Amazon', 'https://m.media-amazon.com/images/I/71-H6pqVjOL._AC_SX679_.jpg', 'Fenix 7X com carregamento solar, vidro de safira e autonomia até 37 dias.', 100, 1, 0, 4, 19);

-- ============= TVS (6 produtos) =============

INSERT INTO produtos (categoria, marca, modelo, nome, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('TVs', 'Samsung', 'Neo QLED 4K 65"', 'Samsung Neo QLED 4K 65"', 1799.00, 1999.00, 'Amazon', 'https://m.media-amazon.com/images/I/81-hGvbdnKL._AC_SX679_.jpg', 'Samsung Neo QLED 65" com tecnologia Quantum Mini LED, 4K e taxa de atualização de 120Hz.', 100, 1, 1, 5, 2),
('TVs', 'LG', 'OLED evo C3 65"', 'LG OLED evo C3 65"', 1999.00, 2299.00, 'Amazon', 'https://m.media-amazon.com/images/I/81qNiCr5fVL._AC_SX679_.jpg', 'LG OLED C3 65" com painel OLED evo, processador α9 Gen6 AI e Dolby Vision.', 100, 1, 1, 5, 13),
('TVs', 'Sony', 'Bravia XR A80L 55"', 'Sony Bravia XR A80L 55"', 1499.00, 1699.00, 'Amazon', 'https://m.media-amazon.com/images/I/81FqI3kxd8L._AC_SX679_.jpg', 'Sony Bravia A80L 55" OLED com processador Cognitive XR e Google TV.', 100, 1, 0, 5, 10),
('TVs', 'LG', 'NanoCell 75" 4K', 'LG NanoCell 75" 4K', 1299.00, 1499.00, 'Amazon', 'https://m.media-amazon.com/images/I/71XZb6JmUNL._AC_SX679_.jpg', 'LG NanoCell 75" com tecnologia NanoCell, 4K e webOS 23.', 100, 0, 0, 5, 13),
('TVs', 'Samsung', 'Crystal UHD 50"', 'Samsung Crystal UHD 50"', 499.00, 599.00, 'Amazon', 'https://m.media-amazon.com/images/I/81HqJOVR6zL._AC_SX679_.jpg', 'Samsung Crystal UHD 50" com PurColor e processador Crystal 4K.', 100, 0, 0, 5, 2),
('TVs', 'Sony', 'Bravia X90L 65"', 'Sony Bravia X90L 65"', 1399.00, 1599.00, 'Amazon', 'https://m.media-amazon.com/images/I/81+eGaXsrsL._AC_SX679_.jpg', 'Sony Bravia X90L 65" Full Array LED com XR Triluminos Pro.', 100, 1, 0, 5, 10);

-- ============= AUDIO (9 produtos) =============

INSERT INTO produtos (categoria, marca, modelo, nome, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Audio', 'Sony', 'WH-1000XM5', 'Sony WH-1000XM5', 379.00, 419.00, 'Amazon', 'https://m.media-amazon.com/images/I/51MO3+2JKbL._AC_SX679_.jpg', 'Auscultadores premium com cancelamento de ruído líder da indústria e 30h de autonomia.', 100, 1, 1, 6, 10),
('Audio', 'Bose', 'QuietComfort Ultra', 'Bose QuietComfort Ultra', 449.00, 499.00, 'Amazon', 'https://m.media-amazon.com/images/I/51uZ9ItFc4L._AC_SX679_.jpg', 'Bose QC Ultra com áudio espacial Immersive e cancelamento de ruído adaptativo.', 100, 1, 1, 6, 20),
('Audio', 'Apple', 'AirPods Pro 2', 'Apple AirPods Pro 2', 249.00, 279.00, 'Amazon', 'https://m.media-amazon.com/images/I/61SUj2aKoEL._AC_SX679_.jpg', 'AirPods Pro 2 com chip H2, cancelamento ativo de ruído e áudio espacial personalizado.', 100, 1, 1, 6, 1),
('Audio', 'JBL', 'Charge 5', 'JBL Charge 5', 179.00, 199.00, 'Amazon', 'https://m.media-amazon.com/images/I/71JVt9G+e4L._AC_SX679_.jpg', 'Coluna Bluetooth portátil com 20h de autonomia, resistência IP67 e powerbank integrado.', 100, 0, 0, 6, 21),
('Audio', 'Sonos', 'Era 100', 'Sonos Era 100', 279.00, 299.00, 'Amazon', 'https://m.media-amazon.com/images/I/51OaRpzGnCL._AC_SX679_.jpg', 'Coluna inteligente Sonos com som estéreo, compatível com Alexa e Google Assistant.', 100, 1, 1, 6, 22),
('Audio', 'Bose', 'SoundLink Flex', 'Bose SoundLink Flex', 149.00, 169.00, 'Amazon', 'https://m.media-amazon.com/images/I/71D7gICGZsL._AC_SX679_.jpg', 'Coluna Bluetooth compacta e robusta com som PositionIQ.', 100, 0, 0, 6, 20),
('Audio', 'Marshall', 'Emberton II', 'Marshall Emberton II', 149.00, 169.00, 'Amazon', 'https://m.media-amazon.com/images/I/71xRJm9NXSL._AC_SX679_.jpg', 'Coluna portátil Marshall com design icónico e 30h de autonomia.', 100, 0, 0, 6, 23),
('Audio', 'JBL', 'Flip 6', 'JBL Flip 6', 129.00, 149.00, 'Amazon', 'https://m.media-amazon.com/images/I/71Y7+Z0Jl6L._AC_SX679_.jpg', 'JBL Flip 6 com som potente, resistência IP67 e 12h de reprodução.', 100, 0, 0, 6, 21),
('Audio', 'Harman Kardon', 'Aura Studio 3', 'Harman Kardon Aura Studio 3', 299.00, 349.00, 'Amazon', 'https://m.media-amazon.com/images/I/61m8uN8cJcL._AC_SX679_.jpg', 'Coluna premium com design elegante, iluminação ambiente e som 360°.', 100, 1, 0, 6, 24);

-- ============= CONSOLAS (6 produtos) =============

INSERT INTO produtos (categoria, marca, modelo, nome, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Consolas', 'Sony', 'PlayStation 5', 'Sony PlayStation 5', 499.00, 549.00, 'Amazon', 'https://m.media-amazon.com/images/I/51FEel9TkPL._AC_SX679_.jpg', 'PS5 com unidade de disco, SSD ultra-rápido de 825GB e retrocompatibilidade PS4.', 100, 1, 0, 7, 10),
('Consolas', 'Sony', 'PlayStation 5 Digital', 'Sony PlayStation 5 Digital', 399.00, 449.00, 'Amazon', 'https://m.media-amazon.com/images/I/51nXcMSWKsL._AC_SX679_.jpg', 'PS5 Digital Edition totalmente digital sem leitor de discos.', 100, 0, 0, 7, 10),
('Consolas', 'Microsoft', 'Xbox Series X', 'Microsoft Xbox Series X', 499.00, 549.00, 'Amazon', 'https://m.media-amazon.com/images/I/51u77MJTcAL._AC_SX679_.jpg', 'Xbox Series X com 1TB SSD, 4K nativo a 60fps e Ray Tracing.', 100, 1, 0, 7, 11),
('Consolas', 'Microsoft', 'Xbox Series S', 'Microsoft Xbox Series S', 299.00, 349.00, 'Amazon', 'https://m.media-amazon.com/images/I/61ELpJlJCGL._AC_SX679_.jpg', 'Xbox Series S compacta e totalmente digital com 512GB SSD.', 100, 0, 0, 7, 11),
('Consolas', 'Nintendo', 'Switch OLED', 'Nintendo Switch OLED', 349.00, 379.00, 'Amazon', 'https://m.media-amazon.com/images/I/61JfsV+v7iL._AC_SX679_.jpg', 'Nintendo Switch OLED com ecrã OLED vibrante de 7 polegadas e base com porta LAN.', 100, 1, 1, 7, 12),
('Consolas', 'Nintendo', 'Switch Lite', 'Nintendo Switch Lite', 199.00, 229.00, 'Amazon', 'https://m.media-amazon.com/images/I/61FjSiAkK0L._AC_SX679_.jpg', 'Switch Lite compacta e leve para jogar em movimento.', 100, 0, 0, 7, 12);

-- ============= FRIGORÍFICOS (6 produtos) =============

INSERT INTO produtos (categoria, marca, modelo, nome, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Frigoríficos', 'Samsung', 'Bespoke 4-Door Flex', 'Samsung Bespoke 4-Door Flex', 2299.00, 2599.00, 'Amazon', 'https://m.media-amazon.com/images/I/61bOAFSC5YL._AC_SX679_.jpg', 'Frigorífico Samsung Bespoke com 4 portas, painéis customizáveis e tecnologia FlexZone.', 100, 1, 1, 8, 2),
('Frigoríficos', 'LG', 'InstaView Door-in-Door', 'LG InstaView Door-in-Door', 1999.00, 2299.00, 'Amazon', 'https://m.media-amazon.com/images/I/51d1V4uxRsL._AC_SX679_.jpg', 'Frigorífico LG com porta transparente InstaView e tecnologia Door-in-Door.', 100, 1, 1, 8, 13),
('Frigoríficos', 'Bosch', 'Serie 6 NoFrost', 'Bosch Serie 6 NoFrost', 1299.00, 1499.00, 'Amazon', 'https://m.media-amazon.com/images/I/51P5WOpPXiL._AC_SX679_.jpg', 'Frigorífico Bosch com tecnologia NoFrost, classe A++ e VitaFresh Pro.', 100, 1, 0, 8, 26),
('Frigoríficos', 'Siemens', 'iQ500 NoFrost', 'Siemens iQ500 NoFrost', 1199.00, 1399.00, 'Amazon', 'https://m.media-amazon.com/images/I/41Lh7z0gZkL._AC_SX679_.jpg', 'Frigorífico Siemens iQ500 com sistema NoFrost e gavetas hyperFresh.', 100, 0, 0, 8, 27),
('Frigoríficos', 'Whirlpool', 'Side-by-Side', 'Whirlpool Side-by-Side', 999.00, 1199.00, 'Amazon', 'https://m.media-amazon.com/images/I/51kSo3tlAeL._AC_SX679_.jpg', 'Frigorífico americano Whirlpool com dispensador de água e gelo.', 100, 0, 0, 8, 28),
('Frigoríficos', 'Beko', 'NeoFrost Dual Cooling', 'Beko NeoFrost Dual Cooling', 799.00, 949.00, 'Amazon', 'https://m.media-amazon.com/images/I/61FMEYx86yL._AC_SX679_.jpg', 'Frigorífico Beko com sistema NeoFrost de refrigeração dupla.', 100, 0, 0, 8, 29);

-- ============= MÁQUINAS DE LAVAR (6 produtos) =============

INSERT INTO produtos (categoria, marca, modelo, nome, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Máquinas de Lavar', 'Bosch', 'Serie 8 i-DOS', 'Bosch Serie 8 i-DOS', 899.00, 1099.00, 'Amazon', 'https://m.media-amazon.com/images/I/61z3xFSl+xL._AC_SX679_.jpg', 'Máquina de lavar roupa Bosch com doseamento automático i-DOS e 9kg de capacidade.', 100, 1, 1, 9, 26),
('Máquinas de Lavar', 'LG', 'AI DD 10.5kg', 'LG AI DD 10.5kg', 799.00, 949.00, 'Amazon', 'https://m.media-amazon.com/images/I/61H-WkC6D5L._AC_SX679_.jpg', 'Máquina de lavar LG com tecnologia AI Direct Drive e 10.5kg de capacidade.', 100, 1, 1, 9, 13),
('Máquinas de Lavar', 'Samsung', 'EcoBubble 9kg', 'Samsung EcoBubble 9kg', 649.00, 799.00, 'Amazon', 'https://m.media-amazon.com/images/I/71pTVqNK7uL._AC_SX679_.jpg', 'Máquina Samsung com tecnologia EcoBubble para lavagem eficiente a baixas temperaturas.', 100, 1, 0, 9, 2),
('Máquinas de Lavar', 'Siemens', 'iQ700 9kg', 'Siemens iQ700 9kg', 799.00, 949.00, 'Amazon', 'https://m.media-amazon.com/images/I/51pNPVXeNPL._AC_SX679_.jpg', 'Máquina Siemens iQ700 com programa speedPerfect e 9kg de capacidade.', 100, 0, 0, 9, 27),
('Máquinas de Lavar', 'Whirlpool', '6th Sense 8kg', 'Whirlpool 6th Sense 8kg', 549.00, 649.00, 'Amazon', 'https://m.media-amazon.com/images/I/61sDy1ZJXGL._AC_SX679_.jpg', 'Máquina Whirlpool com tecnologia 6th Sense e Fresh Care+.', 100, 0, 0, 9, 28),
('Máquinas de Lavar', 'Indesit', 'Push&Wash 7kg', 'Indesit Push&Wash 7kg', 349.00, 429.00, 'Amazon', 'https://m.media-amazon.com/images/I/61Ld6nCL9cL._AC_SX679_.jpg', 'Máquina Indesit com função Push&Wash para ciclos rápidos.', 100, 0, 0, 9, 30);

-- ============= MICRO-ONDAS (5 produtos) =============

INSERT INTO produtos (categoria, marca, modelo, nome, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Micro-ondas', 'Samsung', 'HotBlast 28L', 'Samsung HotBlast 28L', 199.00, 249.00, 'Amazon', 'https://m.media-amazon.com/images/I/81R3l6wf4dL._AC_SX679_.jpg', 'Micro-ondas Samsung com tecnologia HotBlast para aquecimento rápido e uniforme.', 100, 1, 1, 10, 2),
('Micro-ondas', 'LG', 'NeoChef 42L', 'LG NeoChef 42L', 249.00, 299.00, 'Amazon', 'https://m.media-amazon.com/images/I/61OzRPFfz9L._AC_SX679_.jpg', 'Micro-ondas LG NeoChef com Smart Inverter e 42L de capacidade.', 100, 1, 0, 10, 13),
('Micro-ondas', 'Panasonic', 'Inverter 27L', 'Panasonic Inverter 27L', 179.00, 219.00, 'Amazon', 'https://m.media-amazon.com/images/I/71MnbfqLKuL._AC_SX679_.jpg', 'Micro-ondas Panasonic com tecnologia Inverter para cozinhar uniformemente.', 100, 0, 0, 10, 32),
('Micro-ondas', 'Bosch', 'Serie 6 25L', 'Bosch Serie 6 25L', 229.00, 279.00, 'Amazon', 'https://m.media-amazon.com/images/I/71YrD+6ZfIL._AC_SX679_.jpg', 'Micro-ondas Bosch com grill e 25L de capacidade.', 100, 0, 0, 10, 26),
('Micro-ondas', 'Teka', 'MWE 230 G', 'Teka MWE 230 G', 149.00, 189.00, 'Amazon', 'https://m.media-amazon.com/images/I/61tUqTjF5RL._AC_SX679_.jpg', 'Micro-ondas Teka com grill e 23L de capacidade.', 100, 0, 0, 10, 31);

-- ============= ASPIRADORES (6 produtos) =============

INSERT INTO produtos (categoria, marca, modelo, nome, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Aspiradores', 'Dyson', 'V15 Detect', 'Dyson V15 Detect', 649.00, 749.00, 'Amazon', 'https://m.media-amazon.com/images/I/61nv96OQPUL._AC_SX679_.jpg', 'Aspirador sem fios Dyson com tecnologia de deteção de partículas a laser.', 100, 1, 1, 11, 33),
('Aspiradores', 'Dyson', 'V12 Detect Slim', 'Dyson V12 Detect Slim', 499.00, 599.00, 'Amazon', 'https://m.media-amazon.com/images/I/51eJ2xnfQEL._AC_SX679_.jpg', 'Aspirador Dyson V12 compacto e leve com laser Slim Fluffy.', 100, 1, 0, 11, 33),
('Aspiradores', 'Xiaomi', 'Robot Vacuum S10+', 'Xiaomi Robot Vacuum S10+', 449.00, 549.00, 'Amazon', 'https://m.media-amazon.com/images/I/61IhKUYBZxL._AC_SX679_.jpg', 'Aspirador robô Xiaomi com auto-esvaziamento e mapeamento LiDAR.', 100, 1, 1, 11, 16),
('Aspiradores', 'iRobot', 'Roomba Combo j7+', 'iRobot Roomba Combo j7+', 799.00, 999.00, 'Amazon', 'https://m.media-amazon.com/images/I/51zrYivWKbL._AC_SX679_.jpg', 'Roomba j7+ 2-em-1: aspira e lava com auto-esvaziamento.', 100, 1, 1, 11, 35),
('Aspiradores', 'Rowenta', 'X-Force Flex 11.60', 'Rowenta X-Force Flex 11.60', 299.00, 379.00, 'Amazon', 'https://m.media-amazon.com/images/I/61IA7hJjnpL._AC_SX679_.jpg', 'Aspirador sem fios Rowenta flexível para alcançar zonas difíceis.', 100, 0, 0, 11, 34),
('Aspiradores', 'Philips', 'SpeedPro Max', 'Philips SpeedPro Max', 249.00, 319.00, 'Amazon', 'https://m.media-amazon.com/images/I/71kVE6aLKJL._AC_SX679_.jpg', 'Aspirador Philips 360° com tecnologia PowerCyclone.', 100, 0, 0, 11, 14);

-- ============= AR CONDICIONADO (6 produtos) =============

INSERT INTO produtos (categoria, marca, modelo, nome, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Ar Condicionado', 'Daikin', 'Emura 12000 BTU', 'Daikin Emura 12000 BTU', 1299.00, 1499.00, 'Amazon', 'https://m.media-amazon.com/images/I/51mY4zJH6ZL._AC_SX679_.jpg', 'Ar condicionado Daikin Emura com design premium e tecnologia Inverter.', 100, 1, 1, 12, 36),
('Ar Condicionado', 'Mitsubishi', 'MSZ-LN 9000 BTU', 'Mitsubishi MSZ-LN 9000 BTU', 999.00, 1199.00, 'Amazon', 'https://m.media-amazon.com/images/I/31l5P-YjcYL._AC_SX679_.jpg', 'Ar condicionado Mitsubishi com WiFi integrado e classe A+++.', 100, 1, 1, 12, 37),
('Ar Condicionado', 'LG', 'Artcool Mirror 12000 BTU', 'LG Artcool Mirror 12000 BTU', 1099.00, 1299.00, 'Amazon', 'https://m.media-amazon.com/images/I/41wQzQwS8sL._AC_SX679_.jpg', 'LG Artcool com acabamento em espelho e Dual Inverter Compressor.', 100, 1, 0, 12, 13),
('Ar Condicionado', 'Fujitsu', 'ASYG09KXCA 9000 BTU', 'Fujitsu ASYG09KXCA 9000 BTU', 799.00, 949.00, 'Amazon', 'https://m.media-amazon.com/images/I/41SVDCBV5CL._AC_SX679_.jpg', 'Ar condicionado Fujitsu eficiente e silencioso.', 100, 0, 0, 12, 38),
('Ar Condicionado', 'Samsung', 'WindFree Elite 9000 BTU', 'Samsung WindFree Elite 9000 BTU', 949.00, 1149.00, 'Amazon', 'https://m.media-amazon.com/images/I/51MBvz4h8bL._AC_SX679_.jpg', 'Samsung WindFree com distribuição de ar sem correntes.', 100, 1, 1, 12, 2),
('Ar Condicionado', 'Daikin', 'Perfera 9000 BTU', 'Daikin Perfera 9000 BTU', 899.00, 1049.00, 'Amazon', 'https://m.media-amazon.com/images/I/41Zn6PsRqbL._AC_SX679_.jpg', 'Daikin Perfera com purificação de ar e controlo inteligente.', 100, 0, 0, 12, 36);

-- ============= MÁQUINAS DE CAFÉ (6 produtos) =============

INSERT INTO produtos (categoria, marca, modelo, nome, preco, preco_original, loja, imagem, descricao, stock, destaque, novidade, category_id, brand_id) VALUES
('Máquinas de Café', 'De\'Longhi', 'Magnifica S', 'De\'Longhi Magnifica S', 399.00, 499.00, 'Amazon', 'https://m.media-amazon.com/images/I/71VQAuN4OuL._AC_SX679_.jpg', 'Máquina de café expresso automática De\'Longhi com moinho integrado.', 100, 1, 0, 13, 39),
('Máquinas de Café', 'Nespresso', 'Vertuo Next', 'Nespresso Vertuo Next', 179.00, 219.00, 'Amazon', 'https://m.media-amazon.com/images/I/61JaXDtvCcL._AC_SX679_.jpg', 'Nespresso Vertuo com tecnologia de leitura de código de barras.', 100, 1, 1, 13, 40),
('Máquinas de Café', 'Sage', 'Barista Express', 'Sage Barista Express', 599.00, 699.00, 'Amazon', 'https://m.media-amazon.com/images/I/71xCJMtxAyL._AC_SX679_.jpg', 'Máquina Sage com moinho cónico integrado e controlo de temperatura PID.', 100, 1, 1, 13, 41),
('Máquinas de Café', 'De\'Longhi', 'Dedica Style', 'De\'Longhi Dedica Style', 199.00, 249.00, 'Amazon', 'https://m.media-amazon.com/images/I/71MU2L2P2kL._AC_SX679_.jpg', 'Máquina compacta De\'Longhi com apenas 15cm de largura.', 100, 0, 0, 13, 39),
('Máquinas de Café', 'Krups', 'Nespresso Essenza Mini', 'Krups Nespresso Essenza Mini', 89.00, 119.00, 'Amazon', 'https://m.media-amazon.com/images/I/61fBJdR7XeL._AC_SX679_.jpg', 'Nespresso Essenza Mini ultra-compacta para cápsulas.', 100, 0, 0, 13, 42),
('Máquinas de Café', 'Sage', 'Oracle Touch', 'Sage Oracle Touch', 1999.00, 2299.00, 'Amazon', 'https://m.media-amazon.com/images/I/71f-0b5QBHL._AC_SX679_.jpg', 'Máquina profissional Sage com ecrã táctil e texturização automática de leite.', 100, 1, 1, 13, 41);

-- =============================================================================
-- RESUMO DA IMPORTAÇÃO
-- =============================================================================

SELECT 
    '✅ Base de dados criada com sucesso!' AS Status,
    (SELECT COUNT(*) FROM categories) AS Categorias,
    (SELECT COUNT(*) FROM brands) AS Marcas,
    (SELECT COUNT(*) FROM produtos) AS Produtos;

SELECT 
    c.name AS Categoria,
    COUNT(p.id) AS Total_Produtos
FROM categories c
LEFT JOIN produtos p ON p.category_id = c.id
GROUP BY c.id, c.name
ORDER BY c.display_order, c.name;

-- =============================================================================
-- PRONTO! Agora pode aceder a: http://localhost/gomestech/
-- Importe este ficheiro no phpMyAdmin para criar a base de dados completa
-- =============================================================================
