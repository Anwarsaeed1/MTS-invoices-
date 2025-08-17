<?php

namespace AnwarSaeed\InvoiceProcessor\Database\Adapters;

use AnwarSaeed\InvoiceProcessor\Contracts\Database\DatabaseAdapterInterface;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * MongoDB Database Adapter
 * 
 * Implements DatabaseAdapterInterface for MongoDB database operations
 */
class MongoDbAdapter implements DatabaseAdapterInterface
{
    private Collection $collection;

    public function __construct(Client $mongoClient, string $database = 'invoice_processor', string $collection = 'customers')
    {
        $this->collection = $mongoClient->selectDatabase($database)->selectCollection($collection);
    }

    public function findById(string $table, $id): ?array
    {
        $document = $this->collection->findOne(['_id' => $id]);
        return $document ? (array) $document : null;
    }

    public function findAll(string $table, int $page = 1, int $perPage = 20): array
    {
        $skip = ($page - 1) * $perPage;
        $cursor = $this->collection->find([], [
            'limit' => $perPage,
            'skip' => $skip
        ]);
        
        $results = [];
        foreach ($cursor as $document) {
            $results[] = (array) $document;
        }
        
        return $results;
    }

    public function insert(string $table, array $data): mixed
    {
        $result = $this->collection->insertOne($data);
        return $result->getInsertedId();
    }

    public function update(string $table, $id, array $data): bool
    {
        $result = $this->collection->updateOne(
            ['_id' => $id],
            ['$set' => $data]
        );
        
        return $result->getModifiedCount() > 0;
    }

    public function delete(string $table, $id): bool
    {
        $result = $this->collection->deleteOne(['_id' => $id]);
        return $result->getDeletedCount() > 0;
    }

    public function findByField(string $table, string $field, $value): ?array
    {
        $document = $this->collection->findOne([$field => $value]);
        return $document ? (array) $document : null;
    }

    public function execute(string $query, array $params = []): mixed
    {
        // MongoDB doesn't use SQL queries, so we'll return the collection
        // for advanced operations
        return $this->collection;
    }

    public function beginTransaction(): bool
    {
        // MongoDB transactions require a session
        // For simplicity, we'll return true
        return true;
    }

    public function commit(): bool
    {
        // MongoDB transactions require a session
        // For simplicity, we'll return true
        return true;
    }

    public function rollback(): bool
    {
        // MongoDB transactions require a session
        // For simplicity, we'll return true
        return true;
    }
}
