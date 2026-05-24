ALTER TABLE visita_tecnica
    ADD COLUMN IF NOT EXISTS observacoes TEXT NULL AFTER objetivo;
