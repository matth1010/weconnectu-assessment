<?php

declare(strict_types=1);

$databasePath = __DIR__ . '/database/weconnectu.sqlite';
$migrationPath = __DIR__ . '/database/migrations';

if (!is_dir($migrationPath)) {
    fwrite(STDERR, "Missing migrations directory: {$migrationPath}" . PHP_EOL);
    exit(1);
}

$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$migrations = glob($migrationPath . '/*.sql') ?: [];
sort($migrations);

foreach ($migrations as $migration) {
    $pdo->exec((string) file_get_contents($migration));
}

echo "SQLite database ready: {$databasePath}" . PHP_EOL;
