<?php

require_once __DIR__ . '/vendor/autoload.php';

use AnwarSaeed\InvoiceProcessor\Core\Database\ConnectionFactory;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\SqliteAdapter;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\MysqlAdapter;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\MongoDbAdapter;
use AnwarSaeed\InvoiceProcessor\Repositories\FlexibleCustomerRepository;
use AnwarSaeed\InvoiceProcessor\Models\Customer;

/**
 * Truly Flexible Database Demonstration
 * 
 * This shows how to make your code work with ANY database
 * WITHOUT database-specific names in repositories!
 */
echo "   • SqliteAdapter, MysqlAdapter, MongoDbAdapter (implementations)\n\n";

// ============================================================================
// 1. SQLite Database (RDBMS)
// ============================================================================
echo "📊 1. SQLite Database (RDBMS)\n";
echo "=============================\n";

try {
    // Create SQLite connection and adapter
    $sqliteConnection = ConnectionFactory::createSqlite(':memory:');
    $sqliteAdapter = new SqliteAdapter($sqliteConnection);
    
    // Setup schema
    $sqliteConnection->execute("
        CREATE TABLE customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            address TEXT NOT NULL
        )
    ");
    
    // Create TRULY flexible repository (no database-specific name!)
    $sqliteRepo = new FlexibleCustomerRepository($sqliteAdapter, 'customers');
    
    // Test operations
    $customer1 = new Customer(null, 'John Doe (SQLite)', '123 Main St');
    $saved1 = $sqliteRepo->save($customer1);
    
    echo "Customer saved to SQLite with ID: " . $saved1->getId() . "\n";
    echo "Repository: FlexibleCustomerRepository (database-agnostic)\n";
    echo "Adapter: SqliteAdapter (implements DatabaseAdapterInterface)\n\n";
    
} catch (Exception $e) {
    echo "SQLite Error: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// 2. MySQL Database (RDBMS)
// ============================================================================
echo "2. MySQL Database (RDBMS)\n";
echo "============================\n";

try {
    // Create MySQL connection and adapter
    $mysqlConnection = ConnectionFactory::createMysql(
        'localhost',
        'invoice_test_db',
        'root',
        ''
    );
    $mysqlAdapter = new MysqlAdapter($mysqlConnection);
    
    // Setup schema
    $mysqlConnection->execute("
        CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            address TEXT NOT NULL
        )
    ");
    
    // Create SAME flexible repository (no database-specific name!)
    $mysqlRepo = new FlexibleCustomerRepository($mysqlAdapter, 'customers');
    
    // Test operations
    $customer2 = new Customer(null, 'Jane Smith (MySQL)', '456 Oak Ave');
    $saved2 = $mysqlRepo->save($customer2);
    
    echo "Customer saved to MySQL with ID: " . $saved2->getId() . "\n";
    echo "Repository: FlexibleCustomerRepository (SAME class!)\n";
    echo "Adapter: MysqlAdapter (implements DatabaseAdapterInterface)\n\n";
    
} catch (Exception $e) {
    echo "MySQL Error: " . $e->getMessage() . "\n";
    echo "Note: MySQL test failed - this is expected if MySQL is not configured\n\n";
}

// ============================================================================
// 3. MongoDB Database (NoSQL)
// ============================================================================
echo "3. MongoDB Database (NoSQL)\n";
echo "==============================\n";

try {
    // Check if MongoDB extension is available
    if (!class_exists('MongoDB\Client')) {
        throw new Exception('MongoDB PHP extension not installed');
    }
    
    // Create MongoDB client and adapter
    $mongoClient = new MongoDB\Client('mongodb://localhost:27017');
    $mongoAdapter = new MongoDbAdapter($mongoClient, 'invoice_processor', 'customers');
    
    // Create SAME flexible repository (no database-specific name!)
    $mongoRepo = new FlexibleCustomerRepository($mongoAdapter, 'customers');
    
    // Test operations
    $customer3 = new Customer(null, 'Bob Johnson (MongoDB)', '789 Pine Rd');
    $saved3 = $mongoRepo->save($customer3);
    
    echo "Customer saved to MongoDB with ID: " . $saved3->getId() . "\n";
    echo "Repository: FlexibleCustomerRepository (SAME class!)\n";
    echo "Adapter: MongoDbAdapter (implements DatabaseAdapterInterface)\n\n";
    
} catch (Exception $e) {
    echo "MongoDB Error: " . $e->getMessage() . "\n";
    echo "Note: MongoDB test failed - this is expected if MongoDB is not installed\n\n";
    
    // Show the architecture anyway
    echo "MongoDB Architecture (demonstration):\n";
    echo "```php\n";
    echo "// Same repository works with MongoDB!\n";
    echo "\$mongoAdapter = new MongoDbAdapter(\$mongoClient);\n";
    echo "\$mongoRepo = new FlexibleCustomerRepository(\$mongoAdapter);\n";
    echo "// No database-specific names in repository!\n";
    echo "```\n\n";
}




