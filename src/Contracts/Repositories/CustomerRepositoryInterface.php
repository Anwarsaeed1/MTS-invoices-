<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Repositories;

use AnwarSaeed\InvoiceProcessor\Models\Customer;

interface CustomerRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a customer by name, or create a new one if not found
     * @param string $name The name of the customer.
     * @param string $address The address of the customer.
     * @return Customer The customer found or created.
     */
    public function findOrCreate(string $name, string $address): Customer;
    
    /**
     * Find a customer by name
     * @param string $name The name of the customer.
     * @return Customer|null The customer found, or null if no customer was found.  
     */
    public function findByName(string $name): ?Customer;
}
