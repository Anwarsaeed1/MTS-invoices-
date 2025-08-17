<?php

namespace AnwarSaeed\InvoiceProcessor\Database\Adapters;

use AnwarSaeed\InvoiceProcessor\Contracts\Database\DatabaseAdapterInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Database\ConnectionInterface;

/**
 * MySQL Database Adapter
 * 
 * Implements DatabaseAdapterInterface for MySQL database operations
 */
class MysqlAdapter implements DatabaseAdapterInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function findById(string $table, $id): ?array
    {
        $stmt = $this->connection->execute(
            "SELECT * FROM {$table} WHERE id = ?",
            [$id]
        );
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findAll(string $table, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->connection->execute(
            "SELECT * FROM {$table} LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        return $stmt->fetchAll();
    }

    public function insert(string $table, array $data): mixed
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->connection->execute($sql, $data);
        
        return $this->connection->getPdo()->lastInsertId();
    }

    public function update(string $table, $id, array $data): bool
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->connection->execute($sql, $data);
        
        return $stmt->rowCount() > 0;
    }

    public function delete(string $table, $id): bool
    {
        $stmt = $this->connection->execute(
            "DELETE FROM {$table} WHERE id = ?",
            [$id]
        );
        
        return $stmt->rowCount() > 0;
    }

    public function findByField(string $table, string $field, $value): ?array
    {
        $stmt = $this->connection->execute(
            "SELECT * FROM {$table} WHERE {$field} = ?",
            [$value]
        );
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function execute(string $query, array $params = []): mixed
    {
        $stmt = $this->connection->execute($query, $params);
        return $stmt;
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollback();
    }
}
