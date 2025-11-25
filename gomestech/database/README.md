# 📦 GomesTech - Base de Dados

## 🎯 Estrutura Consolidada Única

A base de dados GomesTech está **totalmente consolidada num único ficheiro SQL**.

---

## 📄 Ficheiros Disponíveis

### 🔵 GOMESTECH_COMPLETO.sql
**Ficheiro Principal - ÚNICO E COMPLETO**

Este é o **único ficheiro SQL necessário** para criar toda a base de dados GomesTech.

#### ✨ O que está incluído:

**Estrutura Completa:**
- ✅ 10 Tabelas principais (users, categories, brands, produtos, favoritos, comparacao, carrinho, encomendas, encomenda_itens, promocoes)
- ✅ 13 Categorias pré-definidas
- ✅ 42 Marcas principais
- ✅ 70+ Produtos de exemplo (todas as categorias)
- ✅ 1 Utilizador admin (admin@gomestech.pt / admin123)

**Funcionalidades Avançadas:**
- ✅ 3 Views úteis (produtos populares, estatísticas, baixo stock)
- ✅ 3 Triggers automáticos (gestão de stock, SKU auto)
- ✅ 2 Stored Procedures (criar encomenda, pesquisar produtos)
- ✅ Índices otimizados para performance
- ✅ Foreign Keys configuradas
- ✅ Campos timestamp automáticos
- ✅ UTF-8 (utf8mb4) configurado

**Sistema Completo de E-commerce:**
- Sistema de utilizadores com autenticação
- Catálogo de produtos com categorias e marcas
- Sistema de favoritos/lista de desejos
- Sistema de comparação de produtos
- Carrinho de compras persistente
- Sistema completo de encomendas
- Sistema de promoções temporárias
- Gestão automática de stock
- Cálculo automático de preços e IVA

---

### 🔧 otimizar_database.sql
**Otimizações Adicionais (Opcional)**

Script com otimizações suplementares caso necessário:
- Índices adicionais
- Comandos ANALYZE/OPTIMIZE
- Comentários sobre estrutura

> **Nota:** Só execute este ficheiro se precisar de otimizações adicionais. A base já está otimizada em `GOMESTECH_COMPLETO.sql`.

---

## 🚀 Como Instalar

### Opção 1: phpMyAdmin (Recomendado)
1. Abrir phpMyAdmin
2. Clicar em "Importar"
3. Selecionar `GOMESTECH_COMPLETO.sql`
4. Clicar em "Executar"
5. ✅ Pronto!

### Opção 2: Linha de Comandos MySQL
```bash
mysql -u root -p < database/GOMESTECH_COMPLETO.sql
```

### Opção 3: Via PHP
```bash
php database/importar_catalogo_json.php
```

---

## 📊 Estrutura da Base de Dados

### Tabelas Principais

```
gomestech/
├── 👥 users                    Utilizadores do sistema
├── 📂 categories               Categorias de produtos
├── 🏷️ brands                   Marcas de produtos
├── 📦 produtos                 Catálogo completo
├── ⭐ favoritos                Lista de desejos
├── 🔄 comparacao               Comparação de produtos
├── 🛒 carrinho                 Carrinho de compras
├── 📋 encomendas               Pedidos/Encomendas
├── 📝 encomenda_itens          Itens das encomendas
└── 🏷️ promocoes                Promoções temporárias
```

### Categorias Incluídas

1. 📱 Smartphones
2. 💻 Laptops
3. 📱 Tablets
4. ⌚ Wearables
5. 📺 TVs
6. 🎧 Audio
7. 🎮 Consolas
8. 🧊 Frigoríficos
9. 🌀 Máquinas de Lavar
10. 📦 Micro-ondas
11. 🧹 Aspiradores
12. ❄️ Ar Condicionado
13. ☕ Máquinas de Café

### Marcas Principais (42 total)

**Tecnologia:** Apple, Samsung, Google, OnePlus, Dell, Lenovo, HP, Asus, MSI, Sony, Microsoft, Nintendo, LG, Philips, TP-Link, Xiaomi, Motorola, Realme

**Audio:** Bose, JBL, Sonos, Marshall, Harman Kardon, Amazon

**Eletrodomésticos:** Bosch, Siemens, Whirlpool, Beko, Indesit, Teka, Panasonic, Dyson, Rowenta, iRobot

**Climatização:** Daikin, Mitsubishi, Fujitsu

**Café:** De'Longhi, Nespresso, Sage, Krups

---

## 🔐 Credenciais Padrão

### Utilizador Administrador
- **Email:** admin@gomestech.pt
- **Password:** admin123
- **Tipo:** Administrador (is_admin = 1)

> ⚠️ **Segurança:** Altere a password do admin após a primeira instalação!

---

## 📈 Funcionalidades Automáticas

### Triggers

#### 1. after_encomenda_item_insert
- Atualiza stock automaticamente ao criar item de encomenda
- Calcula subtotal do item

#### 2. after_encomenda_cancelada
- Restaura stock quando encomenda é cancelada
- Reverte as quantidades para o stock

#### 3. before_produto_insert
- Gera SKU automaticamente se não fornecido
- Gera nome do produto (marca + modelo)
- Define preco_original igual a preco se não fornecido

### Views

#### v_produtos_populares
Lista produtos mais vendidos com estatísticas

#### v_stats_encomendas
Estatísticas diárias de encomendas e receita

#### v_produtos_baixo_stock
Produtos com stock inferior a 10 unidades

### Stored Procedures

#### sp_criar_encomenda
Cria encomenda completa a partir do carrinho:
- Calcula subtotal, taxa de envio, IVA
- Copia itens do carrinho
- Limpa carrinho após encomenda
- Retorna ID da encomenda criada

#### sp_pesquisar_produtos
Pesquisa inteligente com relevância:
- Busca em marca, modelo, nome, descrição
- Ordena por relevância
- Filtra por categoria (opcional)

---

## ⚙️ Configuração no config.php

O ficheiro `config.php` já está configurado para conectar à base de dados:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gomestech');
```

**Suporta variáveis de ambiente** (`.env`):
```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASS=
DB_NAME=gomestech
```

---

## 🔍 Verificação Pós-Instalação

Execute estas queries para verificar a instalação:

```sql
-- Verificar tabelas criadas
SELECT COUNT(*) as total_tabelas 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'gomestech' AND TABLE_TYPE = 'BASE TABLE';
-- Esperado: 10

-- Verificar produtos inseridos
SELECT COUNT(*) as total_produtos FROM produtos;
-- Esperado: 70+

-- Verificar categorias
SELECT COUNT(*) as total_categorias FROM categories;
-- Esperado: 13

-- Verificar marcas
SELECT COUNT(*) as total_marcas FROM brands;
-- Esperado: 42

-- Verificar views
SELECT COUNT(*) as total_views 
FROM information_schema.VIEWS 
WHERE TABLE_SCHEMA = 'gomestech';
-- Esperado: 3

-- Verificar triggers
SELECT COUNT(*) as total_triggers 
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'gomestech';
-- Esperado: 3

-- Verificar stored procedures
SELECT COUNT(*) as total_procedures 
FROM information_schema.ROUTINES 
WHERE ROUTINE_SCHEMA = 'gomestech' AND ROUTINE_TYPE = 'PROCEDURE';
-- Esperado: 2
```

---

## 📝 Notas Importantes

### ✅ Características
- **Uma única base de dados:** `gomestech`
- **Character set:** UTF-8 (utf8mb4_unicode_ci)
- **Engine:** InnoDB (transações e foreign keys)
- **Timestamps:** Automáticos (created_at, updated_at)
- **Foreign keys:** Configuradas com ON DELETE CASCADE/RESTRICT
- **Índices:** Otimizados para pesquisas rápidas

### ⚠️ Avisos
- O script **elimina a base de dados existente** (`DROP DATABASE IF EXISTS`)
- Faça **backup** antes de executar em produção
- Password do admin é simples (altere em produção)
- Taxa de envio: €5 (grátis acima de €50)
- IVA configurado a 23% (Portugal)

### 🔧 Manutenção
```sql
-- Otimizar todas as tabelas
OPTIMIZE TABLE users, categories, brands, produtos, 
    favoritos, comparacao, carrinho, encomendas, 
    encomenda_itens, promocoes;

-- Analisar tabelas
ANALYZE TABLE produtos, encomendas;

-- Ver tamanho da base de dados
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'gomestech'
GROUP BY table_schema;
```

---

## 🎉 Resumo

- ✅ **1 ficheiro SQL** para toda a base de dados
- ✅ **10 tabelas** principais
- ✅ **70+ produtos** de exemplo
- ✅ **13 categorias** e **42 marcas**
- ✅ **Automação completa** (triggers, procedures, views)
- ✅ **Performance otimizada** (índices, foreign keys)
- ✅ **Pronto para produção**

---

**Versão:** 2.0 (Consolidada)  
**Última Atualização:** 24 Novembro 2025  
**Autor:** GomesTech Development Team
