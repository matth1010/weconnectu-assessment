# WeConnectU Assessment

Simple PHP contact form assessment.

## What it does

- Add contact entries
- View saved entries
- Search entries
- Edit and delete entries
- Validate South African phone numbers

## Run locally

```powershell
php setup_sqlite.php
php -S localhost:8000 -t public
```

Open:

```text
http://localhost:8000
```

## Approach

I kept the project lightweight and used plain PHP with PDO because the assessment did not need a full framework.

I split the validation and database logic into small classes so the pages stay easier to read and test.

## Notes

I used SQLite for local testing so the project can run quickly without setting up MySQL first.

MySQL/MariaDB can also be used by importing:

```sql
SOURCE database/schema_and_seed.sql;
```

## Tests

```powershell
php tests/run.php
```
