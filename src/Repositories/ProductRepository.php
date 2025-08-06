<?php
namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Models\Product;

class ProductRepository
{
    private Connection $connection;

    /**
     * Initializes a new instance of the ProductRepository class.
     *
     * @param Connection $connection The connection to the database.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Finds a product by its name, or creates one if it doesn't exist.
     *
     * @param string $name The name of the product.
     * @param float $price The price of the product.
     *
     * @return Product The product, either found or created.
     */
    public function findOrCreate(string $name, float $price): Product
    {
        $ex = $this->connection->execute(
            "SELECT id, price FROM products WHERE name = ?",
            [$name]
        );
        
        $data = $ex->fetch();
        
        if ($data) {
            return new Product($data['id'], $name, $data['price']);
        }
        
        $this->connection->execute(
            "INSERT INTO products (name, price) VALUES (?, ?)",
            [$name, $price]
        );
        
        $id = $this->connection->getPdo()->lastInsertId();
        return new Product($id, $name, $price);
    }
}