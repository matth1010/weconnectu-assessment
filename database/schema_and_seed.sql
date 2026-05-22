CREATE DATABASE IF NOT EXISTS sa_contact_form
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sa_contact_form;

DROP TABLE IF EXISTS entries;

CREATE TABLE entries (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_entries_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO entries (name, email, phone, message, created_at) VALUES
    ('Lerato Mokoena', 'lerato.mokoena@example.co.za', '0821234567', 'Please send me more information about your services.', '2026-05-18 09:15:00'),
    ('Thabo Naidoo', 'thabo.naidoo@example.co.za', '+27831234567', 'I would like a follow-up call tomorrow morning.', '2026-05-19 14:30:00'),
    ('Ayesha Jacobs', 'ayesha.jacobs@example.co.za', '0712345678', 'Can someone assist me with pricing and availability?', '2026-05-20 11:45:00');
