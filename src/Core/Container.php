<?php

namespace AnwarSaeed\InvoiceProcessor\Core;

use AnwarSaeed\InvoiceProcessor\Contracts\Database\ConnectionInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\{
    CustomerRepositoryInterface,
    InvoiceRepositoryInterface,
    ProductRepositoryInterface
};
use AnwarSaeed\InvoiceProcessor\Contracts\Services\InvoiceServiceInterface;
use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Repositories\{
    CustomerRepository,
    InvoiceRepository,
    ProductRepository
};
use AnwarSaeed\InvoiceProcessor\Services\{
    InvoiceService,
    ExportService,
    ImportService
};

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function __construct()
    {
        $this->registerDefaultBindings();
    }

    /**
     * Registers the default bindings for the container.
     *
     * These bindings include the database connection, repositories, and services.
     *
     * @return void
     */
    private function registerDefaultBindings(): void
    {
        // Database connection
        $this->bind(ConnectionInterface::class, function () {
            return new Connection("sqlite:" . __DIR__ . "/../../database/invoices.db");
        });

        // Repositories
        $this->bind(CustomerRepositoryInterface::class, function () {
            return new CustomerRepository($this->resolve(ConnectionInterface::class));
        });

        $this->bind(ProductRepositoryInterface::class, function () {
            return new ProductRepository($this->resolve(ConnectionInterface::class));
        });

        $this->bind(InvoiceRepositoryInterface::class, function () {
            return new InvoiceRepository($this->resolve(ConnectionInterface::class));
        });

        // Services
        $this->bind(ExportService::class, function () {
            return new ExportService(
                $this->resolve(InvoiceRepositoryInterface::class),
                $this->resolve(CustomerRepositoryInterface::class)
            );
        });

        $this->bind(ImportService::class, function () {
            return new ImportService(
                $this->resolve(InvoiceRepositoryInterface::class),
                $this->resolve(CustomerRepositoryInterface::class),
                $this->resolve(ProductRepositoryInterface::class)
            );
        });

        $this->bind(InvoiceServiceInterface::class, function () {
            return new InvoiceService(
                $this->resolve(InvoiceRepositoryInterface::class),
                $this->resolve(CustomerRepositoryInterface::class),
                $this->resolve(ProductRepositoryInterface::class),
                $this->resolve(ExportService::class),
                $this->resolve(ImportService::class)
            );
        });
    }

    /**
     * Register a binding for the given abstract class or interface.
     *
     * @param string $abstract The abstract class or interface to bind.
     * @param callable $concrete The concrete implementation of the abstract.
     * @return void
     */
    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }


    /**
     * Resolve the concrete implementation for the given abstract and return a singleton instance.
     *
     * @param string $abstract The abstract class or interface to resolve.
     * @return object The singleton instance of the concrete implementation.
     * @throws \Exception If no binding is found for the given abstract.
     */
    public function resolve(string $abstract): object
    {
        // Return singleton instance if already resolved
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Check if we have a binding for this abstract
        if (!isset($this->bindings[$abstract])) {
            throw new \Exception("No binding found for: {$abstract}");
        }

        // Resolve the concrete implementation
        $concrete = $this->bindings[$abstract];
        $instance = $concrete($this);

        // Store as singleton
        $this->instances[$abstract] = $instance;

        return $instance;
    }

    /**
     * Resolves the concrete implementation of the given abstract.
     *
     * @param string $abstract The abstract type to resolve.
     * @return object The resolved concrete implementation.
     */
    public function make(string $abstract): object
    {
        return $this->resolve($abstract);
    }
}
