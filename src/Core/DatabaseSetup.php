<?php

namespace AnwarSaeed\InvoiceProcessor\Core;

use AnwarSaeed\InvoiceProcessor\Contracts\Database\DatabaseAdapterInterface;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\SqliteAdapter;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\MysqlAdapter;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\MongoDbAdapter;
use AnwarSaeed\InvoiceProcessor\Core\DatabaseManager;

/**
 * Dynamic Database Setup
 * 
 * Automatically creates databases and tables based on .env configuration
 * Supports SQLite, MySQL, and MongoDB
 */
class DatabaseSetup
{
    private DatabaseManager $dbManager;
    private array $config;

    public function __construct()
    {
        $this->dbManager = new DatabaseManager();
        $this->config = require __DIR__ . '/../../config/database.php';
    }

    /**
     * Setup database based on current .env configuration
     */
    public function setup(): array
    {
        $dbType = $this->getCurrentDatabaseType();
        echo "🔧 Setting up database: {$dbType}\n";

        try {
            switch ($dbType) {
                case 'sqlite':
                    return $this->setupSqlite();
                case 'mysql':
                    return $this->setupMysql();
                case 'mongodb':
                    return $this->setupMongodb();
                default:
                    throw new \InvalidArgumentException("Unsupported database type: {$dbType}");
            }
        } catch (\Exception $e) {
            echo "❌ Database setup failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Setup SQLite database
     */
    private function setupSqlite(): array
    {
        echo "📁 Setting up SQLite database...\n";
        
        $dbPath = $this->config['connections']['sqlite']['database'];
        $dbDir = dirname($dbPath);
        
        // Create directory if it doesn't exist
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
            echo "    Created directory: {$dbDir}\n";
        }
        
        // Create database file if it doesn't exist
        if (!file_exists($dbPath)) {
            touch($dbPath);
            echo "    Created database file: {$dbPath}\n";
        }
        
        $adapter = $this->dbManager->createAdapterForType('sqlite');
        $this->createTables($adapter, 'sqlite');
        
        return [
            'type' => 'sqlite',
            'path' => $dbPath,
            'tables' => ['customers', 'products', 'invoices', 'invoice_items']
        ];
    }

    /**
     * Setup MySQL database
     */
    private function setupMysql(): array
    {
        echo "🐬 Setting up MySQL database...\n";
        
        $config = $this->config['connections']['mysql'];
        $database = $config['database'];
        
        // First connect without database to create it
        $pdo = new \PDO(
            "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}",
            $config['username'],
            $config['password']
        );
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "    Database '{$database}' ready\n";
        
        $adapter = $this->dbManager->createAdapterForType('mysql');
        $this->createTables($adapter, 'mysql');
        
        return [
            'type' => 'mysql',
            'database' => $database,
            'tables' => ['customers', 'products', 'invoices', 'invoice_items']
        ];
    }

    /**
     * Setup MongoDB database
     */
    private function setupMongodb(): array
    {
        echo "🍃 Setting up MongoDB database...\n";
        
        $config = $this->config['connections']['mongodb'];
        $database = $config['database'];
        
        try {
            $adapter = $this->dbManager->createAdapterForType('mongodb');
            $this->createCollections($adapter);
            
            return [
                'type' => 'mongodb',
                'database' => $database,
                'collections' => ['customers', 'products', 'invoices', 'invoice_items']
            ];
        } catch (\Exception $e) {
            echo "⚠️  MongoDB setup failed: " . $e->getMessage() . "\n";
            echo "   Please ensure MongoDB is running and accessible\n";
            throw $e;
        }
    }

    /**
     * Create tables for SQLite and MySQL
     */
    private function createTables(DatabaseAdapterInterface $adapter, string $dbType): void
    {
        echo "📋 Creating tables...\n";
        
        $tables = [
            'customers' => $this->getCustomersTableSchema($dbType),
            'products' => $this->getProductsTableSchema($dbType),
            'invoices' => $this->getInvoicesTableSchema($dbType),
            'invoice_items' => $this->getInvoiceItemsTableSchema($dbType)
        ];
        
        foreach ($tables as $tableName => $schema) {
            try {
                $adapter->execute($schema);
                echo "    ✓ Table '{$tableName}' created\n";
            } catch (\Exception $e) {
                // Table might already exist, that's okay
                echo "    ⚠️  Table '{$tableName}' already exists or error: " . $e->getMessage() . "\n";
            }
        }
        
        // Create indexes
        $this->createIndexes($adapter, $dbType);
    }

    /**
     * Create collections for MongoDB
     */
    private function createCollections(DatabaseAdapterInterface $adapter): void
    {
        echo "📋 Creating collections...\n";
        
        $collections = ['customers', 'products', 'invoices', 'invoice_items'];
        
        foreach ($collections as $collection) {
            try {
                // MongoDB collections are created automatically when first document is inserted
                // We'll create an index to ensure the collection exists
                $adapter->execute("CREATE INDEX IF NOT EXISTS idx_{$collection}_id ON {$collection}(_id)");
                echo "    ✓ Collection '{$collection}' ready\n";
            } catch (\Exception $e) {
                echo "    ⚠️  Collection '{$collection}' setup: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Create indexes for better performance
     */
    private function createIndexes(DatabaseAdapterInterface $adapter, string $dbType): void
    {
        echo "🔍 Creating indexes...\n";
        
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_customers_name ON customers(name)",
            "CREATE INDEX IF NOT EXISTS idx_products_name ON products(name)",
            "CREATE INDEX IF NOT EXISTS idx_invoices_customer_id ON invoices(customer_id)",
            "CREATE INDEX IF NOT EXISTS idx_invoices_date ON invoices(invoice_date)",
            "CREATE INDEX IF NOT EXISTS idx_invoice_items_invoice_id ON invoice_items(invoice_id)",
            "CREATE INDEX IF NOT EXISTS idx_invoice_items_product_id ON invoice_items(product_id)"
        ];
        
        foreach ($indexes as $index) {
            try {
                $adapter->execute($index);
            } catch (\Exception $e) {
                // Index might already exist, that's okay
            }
        }
        
        echo "    ✓ Indexes created\n";
    }

    /**
     * Get customers table schema
     */
    private function getCustomersTableSchema(string $dbType): string
    {
        $autoIncrement = $dbType === 'sqlite' ? 'AUTOINCREMENT' : 'AUTO_INCREMENT';
        
        return "
            CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY {$autoIncrement},
                name TEXT NOT NULL,
                address TEXT NOT NULL
            )
        ";
    }

    /**
     * Get products table schema
     */
    private function getProductsTableSchema(string $dbType): string
    {
        $autoIncrement = $dbType === 'sqlite' ? 'AUTOINCREMENT' : 'AUTO_INCREMENT';
        
        return "
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY {$autoIncrement},
                name TEXT NOT NULL,
                price DECIMAL(10,2) NOT NULL
            )
        ";
    }

    /**
     * Get invoices table schema
     */
    private function getInvoicesTableSchema(string $dbType): string
    {
        $autoIncrement = $dbType === 'sqlite' ? 'AUTOINCREMENT' : 'AUTO_INCREMENT';
        
        return "
            CREATE TABLE IF NOT EXISTS invoices (
                id INTEGER PRIMARY KEY {$autoIncrement},
                invoice_date DATE NOT NULL,
                customer_id INTEGER NOT NULL,
                grand_total DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (customer_id) REFERENCES customers(id)
            )
        ";
    }

    /**
     * Get invoice_items table schema
     */
    private function getInvoiceItemsTableSchema(string $dbType): string
    {
        $autoIncrement = $dbType === 'sqlite' ? 'AUTOINCREMENT' : 'AUTO_INCREMENT';
        
        return "
            CREATE TABLE IF NOT EXISTS invoice_items (
                id INTEGER PRIMARY KEY {$autoIncrement},
                invoice_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                total DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ";
    }

    /**
     * Get current database type from environment
     */
    private function getCurrentDatabaseType(): string
    {
        return $_ENV['DB_TYPE'] ?? $this->config['default'] ?? 'sqlite';
    }

    /**
     * Get database statistics
     */
    public function getStats(): array
    {
        $dbType = $this->getCurrentDatabaseType();
        $adapter = $this->dbManager->createAdapter();
        
        $stats = [];
        $tables = ['customers', 'products', 'invoices', 'invoice_items'];
        
        foreach ($tables as $table) {
            try {
                $result = $adapter->execute("SELECT COUNT(*) as count FROM {$table}")->fetch();
                $stats[$table] = $result['count'] ?? 0;
            } catch (\Exception $e) {
                $stats[$table] = 0;
            }
        }
        
        return [
            'database_type' => $dbType,
            'tables' => $stats
        ];
    }

    /**
     * Clear all data from tables (for testing)
     */
    public function clearData(): void
    {
        echo "🧹 Clearing all data...\n";
        
        $adapter = $this->dbManager->createAdapter();
        $tables = ['invoice_items', 'invoices', 'products', 'customers'];
        
        foreach ($tables as $table) {
            try {
                $adapter->execute("DELETE FROM {$table}");
                echo "    ✓ Cleared {$table}\n";
            } catch (\Exception $e) {
                echo "    ⚠️  Could not clear {$table}: " . $e->getMessage() . "\n";
            }
        }
    }
}
