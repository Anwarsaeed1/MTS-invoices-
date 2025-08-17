<?php
namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\CustomerRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Models\Customer;

class CustomerRepository extends AbstractRepository implements CustomerRepositoryInterface
{
    protected string $table = 'customers';
    protected string $entityClass = Customer::class;


    public function __construct(\AnwarSaeed\InvoiceProcessor\Contracts\Database\ConnectionInterface $connection)
    {
        parent::__construct($connection);
    }
    /**
     * Finds a customer by name, or creates a new one if not found
     *
     * @param string $name
     * @param string $address
     *
     * @return Customer
     */

    public function findById(int $id): ?Customer
    {
        $result = parent::findById($id);
        return $result instanceof Customer ? $result : null;
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
        $stmt = $this->connection->execute(
            "SELECT * FROM {$this->table} WHERE name = ?",
            [$name]
        );
        
        $data = $stmt->fetch();
        
        if (!$data) {
            return null;
        }
        
        return $this->createEntityFromData($data);
    }
    
    public function save(object $entity): object
    {
        if (!($entity instanceof Customer)) {
            throw new \InvalidArgumentException('Entity must be a Customer instance');
        }
        
        if ($entity->getId()) {
            // Update existing customer
            $this->connection->execute(
                "UPDATE {$this->table} SET name = ?, address = ? WHERE id = ?",
                [$entity->getName(), $entity->getAddress(), $entity->getId()]
            );
            return $entity;
        } else {
            // Create new customer
            $this->connection->execute(
                "INSERT INTO {$this->table} (name, address) VALUES (?, ?)",
                [$entity->getName(), $entity->getAddress()]
            );
            
            $id = $this->connection->getPdo()->lastInsertId();
            $entity->setId($id);
            return $entity;
        }
    }
    
    protected function createEntityFromData(array $data): object
    {
        return new Customer($data['id'], $data['name'], $data['address']);
    }
}