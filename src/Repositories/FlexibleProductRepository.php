<?php

namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\ProductRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Database\DatabaseAdapterInterface;
use AnwarSaeed\InvoiceProcessor\Models\Product;

/**
 * Flexible Product Repository
 * 
 * This repository is TRULY database-agnostic!
 * It works with ANY database adapter (SQLite, MySQL, MongoDB, Redis, etc.)
 * No database-specific names or SQL queries!
 */
class FlexibleProductRepository implements ProductRepositoryInterface
{
    private DatabaseAdapterInterface $adapter;
    private string $tableName;

    public function __construct(DatabaseAdapterInterface $adapter, string $tableName = 'products')
    {
        $this->adapter = $adapter;
        $this->tableName = $tableName;
    }

    public function findById(int $id): ?Product
    {
        $data = $this->adapter->findById($this->tableName, $id);
        
        if (!$data) {
            return null;
        }
        
        return $this->createEntityFromData($data);
    }

    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $data = $this->adapter->findAll($this->tableName, $page, $perPage);
        
        $products = [];
        foreach ($data as $row) {
            $products[] = $this->createEntityFromData($row);
        }
        
        return $products;
    }

    public function save(object $entity): object
    {
        if (!($entity instanceof Product)) {
            throw new \InvalidArgumentException('Entity must be a Product instance');
        }
        
        $data = [
            'name' => $entity->getName(),
            'price' => $entity->getPrice()
        ];
        
        if ($entity->getId()) {
            // Update existing product
            $this->adapter->update($this->tableName, $entity->getId(), $data);
            return $entity;
        } else {
            // Create new product
            $id = $this->adapter->insert($this->tableName, $data);
            $entity->setId($id);
            return $entity;
        }
    }

    public function delete(int $id): bool
    {
        return $this->adapter->delete($this->tableName, $id);
    }

    public function findOrCreate(string $name, float $price): Product
    {
        $existingProduct = $this->findByName($name);
        
        if ($existingProduct) {
            return $existingProduct;
        }
        
        $product = new Product(null, $name, $price);
        return $this->save($product);
    }

    public function findByName(string $name): ?Product
    {
        $data = $this->adapter->findByField($this->tableName, 'name', $name);
        
        if (!$data) {
            return null;
        }
        
        return $this->createEntityFromData($data);
    }

    protected function createEntityFromData(array $data): object
    {
        return new Product($data['id'], $data['name'], $data['price']);
    }
}
