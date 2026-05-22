DROP TABLE IF EXISTS entries;

CREATE TABLE entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT NOT NULL,
    message TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_entries_created_at ON entries (created_at);

INSERT INTO entries (name, email, phone, message, created_at) VALUES
    ('Lerato Mokoena', 'lerato.mokoena@example.co.za', '0821234567', 'Please send me more information about your services.', '2026-05-18 09:15:00'),
    ('Thabo Naidoo', 'thabo.naidoo@example.co.za', '+27831234567', 'I would like a follow-up call tomorrow morning.', '2026-05-19 14:30:00'),
    ('Ayesha Jacobs', 'ayesha.jacobs@example.co.za', '0712345678', 'Can someone assist me with pricing and availability?', '2026-05-20 11:45:00');
