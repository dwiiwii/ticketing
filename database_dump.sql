-- IT Helpdesk Database Dump
SET FOREIGN_KEY_CHECKS=0;

;

INSERT INTO users (id, email, password, name, avatar, role) VALUES ('1', 'admin@fasremit.com', '123', 'Admin', NULL, 'admin');
INSERT INTO users (id, email, password, name, avatar, role) VALUES ('3', 'finance@fasremit.com', '901', 'Finance', NULL, 'user');
INSERT INTO users (id, email, password, name, avatar, role) VALUES ('4', 'accounting@fasremit.com', '678', 'Accounting', NULL, 'user');
INSERT INTO users (id, email, password, name, avatar, role) VALUES ('6', 'operasional@fasremit.com', '345', 'Operasional', NULL, 'user');

;

INSERT INTO tickets (id, ticket_number, user_id, requester_name, category, priority, subject, description, status, created_at, updated_at, sla, attachment) VALUES ('10', 'FR-1', '6', 'Nita', 'hardware', 'Sedang', 'Komputer Mati', 'Komputer mati mas dwi', 'Selesai', '2026-05-05 09:34:23', '2026-05-05 09:53:55', '1 Jam', '1777948463_fasdeli remiten.png');
INSERT INTO tickets (id, ticket_number, user_id, requester_name, category, priority, subject, description, status, created_at, updated_at, sla, attachment) VALUES ('11', 'FR-11', '4', 'Sapta', 'hardware', 'Sedang', 'Komputer Aneh', 'Komputer saya aneh mas dwi', 'Buka', '2026-05-05 09:38:43', '2026-05-05 09:38:43', NULL, '1777948723_fasdeli remiten.png');
INSERT INTO tickets (id, ticket_number, user_id, requester_name, category, priority, subject, description, status, created_at, updated_at, sla, attachment) VALUES ('12', 'FR-12', '3', 'Aca', 'software', 'Sedang', 'Accurate Error', 'Accurate aku error mas dwi', 'Buka', '2026-05-05 09:46:19', '2026-05-05 09:46:19', NULL, '1777949179_fasdeli remiten.png');

SET FOREIGN_KEY_CHECKS=1;