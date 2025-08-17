<?php
namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\ProductRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Models\Product;

class ProductRepository extends AbstractRepository implements ProductRepositoryInterface
{
    protected string $table = 'products';
    protected string $entityClass = Product::class;

    public function __construct(\AnwarSaeed\InvoiceProcessor\Contracts\Database\ConnectionInterface $connection)
    {
        parent::__construct($connection);
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
        if (!($entity instanceof Product)) {
            throw new \InvalidArgumentException('Entity must be a Product instance');
        }
        
        if ($entity->getId()) {
            // Update existing product
            $this->connection->execute(
                "UPDATE {$this->table} SET name = ?, price = ? WHERE id = ?",
                [$entity->getName(), $entity->getPrice(), $entity->getId()]
            );
            return $entity;
        } else {
            // Create new product
            $this->connection->execute(
                "INSERT INTO {$this->table} (name, price) VALUES (?, ?)",
                [$entity->getName(), $entity->getPrice()]
            );
            
            $id = $this->connection->getPdo()->lastInsertId();
            $entity->setId($id);
            return $entity;
        }
    }
    
    protected function createEntityFromData(array $data): object
    {
        return new Product($data['id'], $data['name'], $data['price']);
    }
}