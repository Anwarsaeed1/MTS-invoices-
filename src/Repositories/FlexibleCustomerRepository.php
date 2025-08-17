<?php

namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\CustomerRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Database\DatabaseAdapterInterface;
use AnwarSaeed\InvoiceProcessor\Models\Customer;

/**
 * Flexible Customer Repository
 * 
 * This repository is TRULY database-agnostic!
 * It works with ANY database adapter (SQLite, MySQL, MongoDB, Redis, etc.)
 * No database-specific names in the class!
 */
class FlexibleCustomerRepository implements CustomerRepositoryInterface
{
    private DatabaseAdapterInterface $adapter;
    private string $tableName;

    public function __construct(DatabaseAdapterInterface $adapter, string $tableName = 'customers')
    {
        $this->adapter = $adapter;
        $this->tableName = $tableName;
    }

    public function findById(int $id): ?Customer
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
        
        $customers = [];
        foreach ($data as $row) {
            $customers[] = $this->createEntityFromData($row);
        }
        
        return $customers;
    }

    public function save(object $entity): object
    {
        if (!($entity instanceof Customer)) {
            throw new \InvalidArgumentException('Entity must be a Customer instance');
        }
        
        $data = [
            'name' => $entity->getName(),
            'address' => $entity->getAddress()
        ];
        
        if ($entity->getId()) {
            // Update existing customer
            $this->adapter->update($this->tableName, $entity->getId(), $data);
            return $entity;
        } else {
            // Create new customer
            $id = $this->adapter->insert($this->tableName, $data);
            $entity->setId($id);
            return $entity;
        }
    }

    public function delete(int $id): bool
    {
        return $this->adapter->delete($this->tableName, $id);
    }

    public function findOrCreate(string $name, string $address): Customer
    {
        $existingCustomer = $this->findByName($name);
        
        if ($existingCustomer) {
            return $existingCustomer;
        }
        
        $customer = new Customer(null, $name, $address);
        return $this->save($customer);
    }

    public function findByName(string $name): ?Customer
    {
        $data = $this->adapter->findByField($this->tableName, 'name', $name);
        
        if (!$data) {
            return null;
        }
        
        return $this->createEntityFromData($data);
    }

    protected function createEntityFromData(array $data): object
    {
        return new Customer($data['id'], $data['name'], $data['address']);
    }
}
