<?php

namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Contracts\Database\ConnectionInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    protected ConnectionInterface $connection;
    protected string $table;
    protected string $entityClass;

    /**
     * AbstractRepository constructor.
     *
     * @param ConnectionInterface $connection The database connection to use.
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Finds an entity by its ID.
     *
     * @param int $id The ID of the entity to find.
     * @return object|null The entity found, or null if no entity was found.
     */
    public function findById(int $id): ?object
    {
        $stmt = $this->connection->execute(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
        
        $data = $stmt->fetch();
        
        if (!$data) {
            return null;
        }
        
        return $this->createEntityFromData($data);
    }

    /**
     * Fetches all entities from the database, paginated.
     *
     * @param int $page The page number to fetch. Defaults to 1.
     * @param int $perPage The number of entities per page. Defaults to 20.
     * @return array An array of entities.
     */
    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->connection->execute(
            "SELECT * FROM {$this->table} LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        $data = $stmt->fetchAll();
        $entities = [];
        
        foreach ($data as $row) {
            $entities[] = $this->createEntityFromData($row);
        }
        
        return $entities;
    }

    /**
     * Deletes an entity from the database by ID.
     *
     * @param int $id The ID of the entity to delete.
     * @return bool True if the delete operation was successful, false otherwise.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->connection->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        );
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Create an entity instance from database data
     */
    abstract protected function createEntityFromData(array $data): object;
}
