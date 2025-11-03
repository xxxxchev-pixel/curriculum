-- ================================================
-- OTIMIZAÇÃO DA BASE DE DADOS GOMESTECH
-- ================================================
-- Este script remove colunas não utilizadas e otimiza a estrutura
-- EXECUTAR APÓS FAZER BACKUP DA BASE DE DADOS!

-- 1. Remover coluna produto_dia (se não estiver a usar pop-up de produto do dia)
-- ALTER TABLE produtos DROP COLUMN IF EXISTS produto_dia;

-- 2. Verificar se tem a tabela promocoes (provavelmente não está a usar)
-- Se não estiver a usar, remover:
-- DROP TABLE IF EXISTS promocoes;

-- 3. Adicionar índices para melhorar performance
ALTER TABLE produtos ADD INDEX idx_categoria (categoria);
ALTER TABLE produtos ADD INDEX idx_marca (marca);
ALTER TABLE produtos ADD INDEX idx_preco (preco);

-- 4. Adicionar índice ao slug para buscas rápidas
ALTER TABLE produtos ADD UNIQUE INDEX idx_slug (slug);

-- 5. Otimizar a tabela
OPTIMIZE TABLE produtos;

-- ================================================
-- ESTRUTURA FINAL RECOMENDADA DA TABELA PRODUTOS:
-- ================================================
-- id (INT, PRIMARY KEY, AUTO_INCREMENT)
-- marca (VARCHAR)
-- modelo (VARCHAR)
-- preco (DECIMAL) - Agora com IVA incluído
-- preco_sem_iva (DECIMAL) - Backup do preço sem IVA
-- iva_incluido (BOOLEAN) - Flag para controle
-- preco_original (DECIMAL) - Para promoções (opcional)
-- desconto_promocao (INT) - Percentagem de desconto (opcional)
-- categoria (VARCHAR) - Com índice
-- imagem (VARCHAR)
-- descricao (TEXT)
-- loja (VARCHAR)
-- slug (VARCHAR) - Com índice único
-- [Especificações técnicas por categoria - opcional]

-- ================================================
-- NOTA IMPORTANTE:
-- ================================================
-- Antes de executar, verificar:
-- 1. Tem backup da base de dados?
-- 2. Quais colunas realmente usa no site?
-- 3. Está a usar sistema de promoções?
-- 4. Está a usar produto do dia?
