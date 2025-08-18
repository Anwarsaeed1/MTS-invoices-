<?php

namespace AnwarSaeed\InvoiceProcessor\Core\Database;

use AnwarSaeed\InvoiceProcessor\Contracts\Database\ConnectionInterface;
use AnwarSaeed\InvoiceProcessor\Database\Connection;

class ConnectionFactory
{
    public static function create(string $type, array $config): ConnectionInterface
    {
        return match ($type) {
            'sqlite' => new Connection($config['dsn']),
            'mysql' => new Connection(
                $config['dsn'],
                $config['username'] ?? null,
                $config['password'] ?? null,
                $config['options'] ?? []
            ),
            'pgsql' => new Connection(
                $config['dsn'],
                $config['username'] ?? null,
                $config['password'] ?? null,
                $config['options'] ?? []
            ),
            default => throw new \InvalidArgumentException("Unsupported database type: {$type}")
        };
    }
    
    public static function createSqlite(string $path): ConnectionInterface
    {
        return new Connection("sqlite:{$path}");
    }
    
    public static function createMysql(
        string $host,
        string $database,
        string $username,
        string $password,
        array $options = []
    ): ConnectionInterface {
        $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
        return new Connection($dsn, $username, $password, $options);
    }
    
    public static function createMongoDb(
        string $host,
        string $database,
        string $username = '',
        string $password = '',
        array $options = []
    ): ConnectionInterface {
        $dsn = "mongodb://{$host}/{$database}";
        return new Connection($dsn, $username, $password, $options);
    }
}
