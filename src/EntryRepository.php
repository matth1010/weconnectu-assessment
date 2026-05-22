<?php

declare(strict_types=1);

class ContactSubmissionRepository
{
    private const TABLE = 'contact_submissions';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function saveSubmission(array $values): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO ' . self::TABLE . ' (name, email, phone, message) VALUES (:name, :email, :phone, :message)'
        );

        $stmt->execute([
            'name' => $values['name'],
            'email' => strtolower($values['email']),
            'phone' => ContactSubmissionValidator::normalizePhone($values['phone']),
            'message' => $values['message'],
        ]);
    }

    public function allSubmissions(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, email, phone, message, created_at FROM ' . self::TABLE . ' ORDER BY created_at DESC, id DESC'
        );

        return $stmt->fetchAll();
    }

    public function searchSubmissions(string $search, int $limit, int $offset): array
    {
        $params = [
            'limit' => $limit,
            'offset' => $offset,
        ];
        $where = '';

        if ($search !== '') {
            // This is dashboard search, so I search the visible contact fields rather than adding separate filters.
            $where = 'WHERE name LIKE :search OR email LIKE :search OR phone LIKE :search OR message LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, phone, message, created_at
             FROM ' . self::TABLE . "
             {$where}
             ORDER BY created_at DESC, id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countSubmissions(string $search = ''): int
    {
        $where = '';
        $params = [];

        if ($search !== '') {
            $where = 'WHERE name LIKE :search OR email LIKE :search OR phone LIKE :search OR message LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ' . self::TABLE . " {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function latestSubmittedAt(): ?string
    {
        $stmt = $this->pdo->query('SELECT created_at FROM ' . self::TABLE . ' ORDER BY created_at DESC, id DESC LIMIT 1');
        $value = $stmt->fetchColumn();

        return $value === false ? null : (string) $value;
    }

    public function countUniqueContactEmails(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(DISTINCT email) FROM ' . self::TABLE);

        return (int) $stmt->fetchColumn();
    }

    public function findSubmission(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, phone, message, created_at FROM ' . self::TABLE . ' WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $submission = $stmt->fetch();

        return $submission === false ? null : $submission;
    }

    public function updateSubmission(int $id, array $values): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE ' . self::TABLE . ' SET name = :name, email = :email, phone = :phone, message = :message WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'name' => $values['name'],
            'email' => strtolower($values['email']),
            'phone' => ContactSubmissionValidator::normalizePhone($values['phone']),
            'message' => $values['message'],
        ]);
    }

    public function deleteSubmission(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

final class EntryRepository extends ContactSubmissionRepository
{
    public function save(array $values): void
    {
        $this->saveSubmission($values);
    }

    public function all(): array
    {
        return $this->allSubmissions();
    }

    public function paginated(string $search, int $limit, int $offset): array
    {
        return $this->searchSubmissions($search, $limit, $offset);
    }

    public function countAll(string $search = ''): int
    {
        return $this->countSubmissions($search);
    }

    public function latestCreatedAt(): ?string
    {
        return $this->latestSubmittedAt();
    }

    public function countUniqueEmails(): int
    {
        return $this->countUniqueContactEmails();
    }

    public function find(int $id): ?array
    {
        return $this->findSubmission($id);
    }

    public function update(int $id, array $values): void
    {
        $this->updateSubmission($id, $values);
    }

    public function delete(int $id): void
    {
        $this->deleteSubmission($id);
    }
}
