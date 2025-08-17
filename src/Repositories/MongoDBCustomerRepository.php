<?php

namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\CustomerRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Models\Customer;
use MongoDB\Client;
use MongoDB\Collection;

class MongoDBCustomerRepository implements CustomerRepositoryInterface
{
    private Collection $collection;

    public function __construct(Client $mongoClient, string $database = 'invoice_processor', string $collection = 'customers')
    {
        $this->collection = $mongoClient->selectDatabase($database)->selectCollection($collection);
    }

    public function findById(int $id): ?Customer
    {
        $document = $this->collection->findOne(['_id' => $id]);
        
        if (!$document) {
            return null;
        }
        
        return $this->createEntityFromData([
            'id' => $document['_id'],
            'name' => $document['name'],
            'address' => $document['address']
        ]);
    }

    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $skip = ($page - 1) * $perPage;
        $cursor = $this->collection->find([], [
            'limit' => $perPage,
            'skip' => $skip
        ]);
        
        $customers = [];
        foreach ($cursor as $document) {
            $customers[] = $this->createEntityFromData([
                'id' => $document['_id'],
                'name' => $document['name'],
                'address' => $document['address']
            ]);
        }
        
        return $customers;
    }

    public function save(object $entity): object
    {
        if (!($entity instanceof Customer)) {
            throw new \InvalidArgumentException('Entity must be a Customer instance');
        }
        
        if ($entity->getId()) {
            // Update existing customer
            $this->collection->updateOne(
                ['_id' => $entity->getId()],
                [
                    '$set' => [
                        'name' => $entity->getName(),
                        'address' => $entity->getAddress()
                    ]
                ]
            );
            return $entity;
        } else {
            // Create new customer
            $result = $this->collection->insertOne([
                'name' => $entity->getName(),
                'address' => $entity->getAddress()
            ]);
            
            $entity->setId($result->getInsertedId());
            return $entity;
        }
    }

    public function delete(int $id): bool
    {
        $result = $this->collection->deleteOne(['_id' => $id]);
        return $result->getDeletedCount() > 0;
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
        $document = $this->collection->findOne(['name' => $name]);
        
        if (!$document) {
            return null;
        }
        
        return $this->createEntityFromData([
            'id' => $document['_id'],
            'name' => $document['name'],
            'address' => $document['address']
        ]);
    }

    protected function createEntityFromData(array $data): object
    {
        return new Customer($data['id'], $data['name'], $data['address']);
    }
}
