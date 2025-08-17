<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Database;

use PDO;
use PDOStatement;

interface ConnectionInterface
{
    /**
     * Get the underlying PDO instance
     */
    public function getPdo(): PDO;
    
    /**
     * Execute a prepared statement
     */
    public function execute(string $sql, array $params = []): PDOStatement;
    
    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool;
    
    /**
     * Commit a transaction
     */
    public function commit(): bool;
    
    /**
     * Rollback a transaction
     */
    public function rollback(): bool;
}
