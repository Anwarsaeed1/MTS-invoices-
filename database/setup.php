<?php

require_once __DIR__ . '/../vendor/autoload.php';

use AnwarSaeed\InvoiceProcessor\Database\Connection;

/**
 * Database Setup Script
 * 
 * This script creates an empty SQLite database with all required tables
 * based on the ERD design for the invoice processor.
 */

class DatabaseSetup
{
    private string $dbPath;
    private Connection $connection;

    public function __construct(string $dbPath = null)
    {
        $this->dbPath = $dbPath ?? __DIR__ . '/invoices.db';
        $this->connection = new Connection("sqlite:{$this->dbPath}");
    }

    /**
     * Create the database and all tables
     */
    public function setup(): void
    {
        echo "🗄️ Setting up database: {$this->dbPath}\n";
        
        try {
            $this->createTables();
            $this->createIndexes();
            $this->verifySetup();
            
            echo "✅ Database setup completed successfully!\n";
            echo "📊 Tables created:\n";
            echo "   - customers\n";
            echo "   - products\n";
            echo "   - invoices\n";
            echo "   - invoice_items\n";
            
        } catch (Exception $e) {
            echo "❌ Database setup failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Create all database tables
     */
    private function createTables(): void
    {
        echo "📋 Creating tables...\n";

        // Create customers table
        $this->connection->execute("
            CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                address TEXT NOT NULL
            )
        ");
        echo "   ✅ customers table created\n";

        // Create products table
        $this->connection->execute("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                price DECIMAL(10,2) NOT NULL
            )
        ");
        echo "   ✅ products table created\n";

        // Create invoices table
        $this->connection->execute("
            CREATE TABLE IF NOT EXISTS invoices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_date DATE NOT NULL,
                customer_id INTEGER NOT NULL,
                grand_total DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (customer_id) REFERENCES customers(id)
            )
        ");
        echo "   ✅ invoices table created\n";

        // Create invoice_items table
        $this->connection->execute("
            CREATE TABLE IF NOT EXISTS invoice_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                total DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ");
        echo "   ✅ invoice_items table created\n";
    }

    /**
     * Create database indexes for better performance
     */
    private function createIndexes(): void
    {
        echo "🔍 Creating indexes...\n";

        // Indexes for better query performance
        $this->connection->execute("
            CREATE INDEX IF NOT EXISTS idx_customers_name ON customers(name)
        ");
        
        $this->connection->execute("
            CREATE INDEX IF NOT EXISTS idx_products_name ON products(name)
        ");
        
        $this->connection->execute("
            CREATE INDEX IF NOT EXISTS idx_invoices_customer_id ON invoices(customer_id)
        ");
        
        $this->connection->execute("
            CREATE INDEX IF NOT EXISTS idx_invoices_date ON invoices(invoice_date)
        ");
        
        $this->connection->execute("
            CREATE INDEX IF NOT EXISTS idx_invoice_items_invoice_id ON invoice_items(invoice_id)
        ");
        
        $this->connection->execute("
            CREATE INDEX IF NOT EXISTS idx_invoice_items_product_id ON invoice_items(product_id)
        ");

        echo "   ✅ Indexes created\n";
    }

    /**
     * Verify that all tables were created successfully
     */
    private function verifySetup(): void
    {
        echo "🔍 Verifying setup...\n";

        $tables = ['customers', 'products', 'invoices', 'invoice_items'];
        
        foreach ($tables as $table) {
            $result = $this->connection->execute("
                SELECT name FROM sqlite_master 
                WHERE type='table' AND name=?
            ", [$table])->fetch();
            
            if (!$result) {
                throw new Exception("Table '{$table}' was not created successfully");
            }
            
            echo "   ✅ Table '{$table}' verified\n";
        }
    }

    /**
     * Get database statistics
     */
    public function getStats(): array
    {
        $stats = [];
        
        foreach (['customers', 'products', 'invoices', 'invoice_items'] as $table) {
            $result = $this->connection->execute("SELECT COUNT(*) as count FROM {$table}")->fetch();
            $stats[$table] = $result['count'];
        }
        
        return $stats;
    }

    /**
     * Clear all data from tables (for testing)
     */
    public function clearData(): void
    {
        echo "🧹 Clearing all data...\n";
        
        $this->connection->execute("DELETE FROM invoice_items");
        $this->connection->execute("DELETE FROM invoices");
        $this->connection->execute("DELETE FROM products");
        $this->connection->execute("DELETE FROM customers");
        
        echo "   ✅ All data cleared\n";
    }
}

// Run setup if script is executed directly
if (php_sapi_name() === 'cli') {
    $setup = new DatabaseSetup();
    $setup->setup();
    
    $stats = $setup->getStats();
    echo "\n📊 Database Statistics:\n";
    foreach ($stats as $table => $count) {
        echo "   {$table}: {$count} records\n";
    }
}
