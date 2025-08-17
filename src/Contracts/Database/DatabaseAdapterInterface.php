<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Database;

/**
 * Database Adapter Interface
 * 
 * This interface abstracts database operations, making repositories
 * truly database-agnostic. Any database can implement this interface.
 */
interface DatabaseAdapterInterface
{
    /**
     * Find a single record by ID
     */
    public function findById(string $table, $id): ?array;
    
    /**
     * Find all records with pagination
     */
    public function findAll(string $table, int $page = 1, int $perPage = 20): array;
    
    /**
     * Insert a new record
     */
    public function insert(string $table, array $data): mixed;
    
    /**
     * Update an existing record
     */
    public function update(string $table, $id, array $data): bool;
    
    /**
     * Delete a record by ID
     */
    public function delete(string $table, $id): bool;
    
    /**
     * Find a record by field value
     */
    public function findByField(string $table, string $field, $value): ?array;
    
    /**
     * Execute raw query
     */
    public function execute(string $query, array $params = []): mixed;
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool;
    
    /**
     * Commit transaction
     */
    public function commit(): bool;
    
    /**
     * Rollback transaction
     */
    public function rollback(): bool;
}
