# Build notes

I kept this as plain PHP because the assessment did not need a full framework.

I used PDO prepared statements for the database work so submitted contact details are not placed directly into SQL strings.

SQLite is included so the project can be tested quickly without setting up MySQL first.

Duplicate email submissions are allowed. A contact may submit the form more than once with a different message.

South African phone numbers are normalised before saving so local and +27 formats are stored consistently.

Search checks name, email, phone and message because the dashboard is meant for quick lookup rather than strict reporting.
