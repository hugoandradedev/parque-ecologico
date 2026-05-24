-- Correcoes para visita tecnica no InfinityFree
-- Execute no phpMyAdmin, dentro do banco usado pelo arquivo .env.

-- 1. Garante que transacoes funcionem corretamente no cadastro de visitas.
ALTER TABLE visita_tecnica
    ENGINE=InnoDB,
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 2. Garante as colunas esperadas pelo codigo atual, sem erro se ja existirem.
SET @tem_guia_id := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'visita_tecnica'
      AND COLUMN_NAME = 'guia_id'
);

SET @sql_guia_id := IF(
    @tem_guia_id = 0,
    'ALTER TABLE visita_tecnica ADD COLUMN guia_id int(11) NOT NULL AFTER created_at',
    'SELECT "Coluna guia_id ja existe" AS info'
);

PREPARE stmt_guia_id FROM @sql_guia_id;
EXECUTE stmt_guia_id;
DEALLOCATE PREPARE stmt_guia_id;

SET @tem_observacoes := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'visita_tecnica'
      AND COLUMN_NAME = 'observacoes'
);

SET @sql_observacoes := IF(
    @tem_observacoes = 0,
    'ALTER TABLE visita_tecnica ADD COLUMN observacoes text DEFAULT NULL AFTER objetivo',
    'SELECT "Coluna observacoes ja existe" AS info'
);

PREPARE stmt_observacoes FROM @sql_observacoes;
EXECUTE stmt_observacoes;
DEALLOCATE PREPARE stmt_observacoes;

-- 3. Garante que exista pelo menos um guia ativo para aparecer no formulario.
-- Se voce ja cadastrou guias pelo painel, pode pular este INSERT.
INSERT INTO guias (nome, email, telefone, especialidade, ativo)
SELECT 'Guia Tecnico', NULL, NULL, 'Educacao ambiental', 1
WHERE NOT EXISTS (
    SELECT 1 FROM guias WHERE ativo = 1
);

-- 4. Conferencias finais.
SELECT COUNT(*) AS guias_ativos FROM guias WHERE ativo = 1;
SHOW COLUMNS FROM visita_tecnica LIKE 'guia_id';
SHOW COLUMNS FROM visita_tecnica LIKE 'observacoes';
SHOW TABLE STATUS LIKE 'visita_tecnica';
