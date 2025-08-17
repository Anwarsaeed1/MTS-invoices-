<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Repositories;

interface RepositoryInterface
{
    /**
     * Find an entity by ID
     * @param int $id The ID of the entity to find.
     * @return object|null The entity found, or null if no entity was found.
     */
    public function findById(int $id): ?object;
    
    /**
     * Find all entities with optional pagination
     * @param int $page The page number to fetch. Defaults to 1.
     * @param int $perPage The number of entities per page. Defaults to 20.
     * @return array An array of entities.
     */
    public function findAll(int $page = 1, int $perPage = 20): array;
    
    /**
     * Save an entity (create or update)
     * @param object $entity The entity to save.
     * @return object The saved entity.
     */
    public function save(object $entity): object;
    
    /**
     * Delete an entity by ID
     * @param int $id The ID of the entity to delete.
     * @return bool True if the delete operation was successful, false otherwise.
     */
    public function delete(int $id): bool;
}
