INSERT INTO contact_submissions (name, email, phone, message, created_at)
SELECT 'Lerato Mokoena', 'lerato.mokoena@example.co.za', '0821234567', 'Please send me more information about your services.', '2026-05-18 09:15:00'
WHERE NOT EXISTS (SELECT 1 FROM contact_submissions WHERE email = 'lerato.mokoena@example.co.za');

INSERT INTO contact_submissions (name, email, phone, message, created_at)
SELECT 'Thabo Naidoo', 'thabo.naidoo@example.co.za', '+27831234567', 'I would like a follow-up call tomorrow morning.', '2026-05-19 14:30:00'
WHERE NOT EXISTS (SELECT 1 FROM contact_submissions WHERE email = 'thabo.naidoo@example.co.za');

INSERT INTO contact_submissions (name, email, phone, message, created_at)
SELECT 'Ayesha Jacobs', 'ayesha.jacobs@example.co.za', '0712345678', 'Can someone assist me with pricing and availability?', '2026-05-20 11:45:00'
WHERE NOT EXISTS (SELECT 1 FROM contact_submissions WHERE email = 'ayesha.jacobs@example.co.za');
