<?php

namespace AnwarSaeed\InvoiceProcessor\Core;

use AnwarSaeed\InvoiceProcessor\Contracts\Database\ConnectionInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Database\DatabaseAdapterInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\{
    CustomerRepositoryInterface,
    InvoiceRepositoryInterface,
    ProductRepositoryInterface
};
use AnwarSaeed\InvoiceProcessor\Contracts\Services\InvoiceServiceInterface;
use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\SqliteAdapter;
use AnwarSaeed\InvoiceProcessor\Core\DatabaseManager;
use AnwarSaeed\InvoiceProcessor\Repositories\{
    FlexibleCustomerRepository,
    FlexibleProductRepository,
    FlexibleInvoiceRepository
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
    private DynamicEnvironmentLoader $envLoader;

    public function __construct()
    {
        // Load environment variables dynamically
        $this->envLoader = new DynamicEnvironmentLoader();
        
        $this->registerDefaultBindings();
    }

    /**
     * Registers the default bindings for the container.
     *
     * These bindings include the database connection, adapters, flexible repositories, and services.
     * Now using database-agnostic repositories with adapter pattern for maximum flexibility.
     *
     * @return void
     */
    private function registerDefaultBindings(): void
    {
        // Database manager for automatic database detection and switching
        $this->bind(DatabaseManager::class, function () {
            return new DatabaseManager();
        });

        // Database connection
        $this->bind(ConnectionInterface::class, function () {
            return new Connection("sqlite:" . __DIR__ . "/../../database/invoices.db");
        });

        // Database adapter (automatically detected and created)
        $this->bind(DatabaseAdapterInterface::class, function () {
            $dbManager = $this->resolve(DatabaseManager::class);
            return $dbManager->createAdapter();
        });

        // Flexible repositories (database-agnostic)
        $this->bind(CustomerRepositoryInterface::class, function () {
            $adapter = $this->resolve(DatabaseAdapterInterface::class);
            return new FlexibleCustomerRepository($adapter, 'customers');
        });

        $this->bind(ProductRepositoryInterface::class, function () {
            $adapter = $this->resolve(DatabaseAdapterInterface::class);
            return new FlexibleProductRepository($adapter, 'products');
        });

        $this->bind(InvoiceRepositoryInterface::class, function () {
            $adapter = $this->resolve(DatabaseAdapterInterface::class);
            return new FlexibleInvoiceRepository($adapter, 'invoices');
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

    /**
     * Switch database adapter for all repositories.
     * This allows easy switching between different database types.
     *
     * @param DatabaseAdapterInterface $adapter The new database adapter
     * @return void
     */
    public function switchDatabaseAdapter(DatabaseAdapterInterface $adapter): void
    {
        // Clear existing instances to force recreation with new adapter
        unset($this->instances[DatabaseAdapterInterface::class]);
        unset($this->instances[CustomerRepositoryInterface::class]);
        unset($this->instances[ProductRepositoryInterface::class]);
        unset($this->instances[InvoiceRepositoryInterface::class]);
        unset($this->instances[ExportService::class]);
        unset($this->instances[ImportService::class]);
        unset($this->instances[InvoiceServiceInterface::class]);

        // Rebind the adapter
        $this->bind(DatabaseAdapterInterface::class, function () use ($adapter) {
            return $adapter;
        });
    }

    /**
     * Switch database type for all repositories.
     * This allows easy switching between different database types using DatabaseManager.
     *
     * @param string $dbType The database type to switch to (sqlite, mysql, mongodb)
     * @return void
     */
    public function switchDatabaseType(string $dbType): void
    {
        $dbManager = $this->resolve(DatabaseManager::class);
        $adapter = $dbManager->switchDatabase($dbType);
        $this->switchDatabaseAdapter($adapter);
    }

    /**
     * Get current database information
     *
     * @return array Database information
     */
    public function getDatabaseInfo(): array
    {
        $dbManager = $this->resolve(DatabaseManager::class);
        return $dbManager->getDatabaseInfo();
    }
}
