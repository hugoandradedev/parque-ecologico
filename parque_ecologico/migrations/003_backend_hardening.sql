-- Migration: backend hardening for reservations, visits and admin operations
-- Review existing duplicate data before adding UNIQUE constraints in production.

ALTER TABLE agendamento
    MODIFY status ENUM('pendente', 'aprovado', 'rejeitado', 'cancelado') NOT NULL DEFAULT 'pendente';

ALTER TABLE visita_tecnica
    MODIFY status ENUM('pendente', 'aprovado', 'rejeitado', 'cancelado') NOT NULL DEFAULT 'pendente';

CREATE INDEX IF NOT EXISTS idx_agendamento_disponibilidade
    ON agendamento (data_reserva, quiosque_id, status, horario_entrada, horario_saida);

CREATE INDEX IF NOT EXISTS idx_agendamento_email
    ON agendamento (email);

CREATE INDEX IF NOT EXISTS idx_visita_disponibilidade
    ON visita_tecnica (data_visita, guia_id, status, horario_entrada, horario_saida);

CREATE INDEX IF NOT EXISTS idx_visita_email
    ON visita_tecnica (email);

CREATE INDEX IF NOT EXISTS idx_visita_guia
    ON visita_tecnica (guia_id);

CREATE INDEX IF NOT EXISTS idx_mensagens_status
    ON mensagens (lida, respondida, criado_em);
