<?php
namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Models\Customer;

class CustomerRepository
{
    private Connection $connection;


    /**
     * Initializes a new instance of the CustomerRepository class.
     *
     * @param Connection $connection The connection to the database.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    /**
     * Finds a customer by name, or creates a new one if not found
     *
     * @param string $name
     * @param string $address
     *
     * @return Customer
     */

    /**
     * Finds a customer by ID
     *
     * @param int $id
     * @return Customer|null
     */
    public function findById(int $id): ?Customer
    {
        $ex = $this->connection->execute(
            "SELECT id, name, address FROM customers WHERE id = ?",
            [$id]
        );
        
        $data = $ex->fetch();
        
        if (!$data) {
            return null;
        }
        
        return new Customer($data['id'], $data['name'], $data['address']);
    }

    /**
     * Finds a customer by name, or creates a new one if not found
     *
     * @param string $name
     * @param string $address
     *
     * @return Customer
     */
    public function findOrCreate(string $name, string $address): Customer
    {
        $ex = $this->connection->execute(
            "SELECT id FROM customers WHERE name = ?",
            [$name]
        );
        
        $data = $ex->fetch();
        
        if ($data) {
            return new Customer($data['id'], $name, $address);
        }
        
        $this->connection->execute(
            "INSERT INTO customers (name, address) VALUES (?, ?)",
            [$name, $address]
        );
        
        $id = $this->connection->getPdo()->lastInsertId();
        return new Customer($id, $name, $address);
    }
}