<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Repositories;

use AnwarSaeed\InvoiceProcessor\Models\Product;

interface ProductRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a product by name, or create a new one if not found
     * @param string $name The name of the product.
     * @param float $price The price of the product.
     * @return Product The product found or created.
     */
    public function findOrCreate(string $name, float $price): Product;
    
    /**
     * Find a product by name
     * @param string $name The name of the product.
     * @return Product|null The product found, or null if no product was found.
     */
    public function findByName(string $name): ?Product;
}
