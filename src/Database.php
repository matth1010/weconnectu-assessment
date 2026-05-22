<?php

declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require __DIR__ . '/config.php';
        $db = $config['db'];
        $driver = strtolower((string) $db['driver']);
        $dsn = $driver === 'sqlite'
            ? 'sqlite:' . $db['sqlite_path']
            : sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $db['host'],
                $db['port'],
                $db['name'],
                $db['charset']
            );

        self::$connection = new PDO($dsn, $driver === 'sqlite' ? null : $db['user'], $driver === 'sqlite' ? null : $db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }
}
