<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EntryRepositoryTest extends TestCase
{
    private EntryRepository $repository;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec(
            'CREATE TABLE entries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                phone TEXT NOT NULL,
                message TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $this->repository = new EntryRepository($pdo);
    }

    public function testCreateReadUpdateDeleteFlow(): void
    {
        $payload = [
            'name' => 'Lerato Mokoena',
            'email' => 'lerato@example.co.za',
            'phone' => '+27 82 123 4567',
            'message' => 'Original message.',
        ];

        $this->repository->save($payload);
        $entries = $this->repository->all();

        self::assertCount(1, $entries);
        self::assertSame('+27821234567', $entries[0]['phone']);

        $id = (int) $entries[0]['id'];
        $this->repository->update($id, [...$payload, 'name' => 'Updated User']);

        self::assertSame('Updated User', $this->repository->find($id)['name'] ?? '');

        $this->repository->delete($id);
        self::assertNull($this->repository->find($id));
    }
}
