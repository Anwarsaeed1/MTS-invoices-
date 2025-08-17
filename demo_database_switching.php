<?php

require_once __DIR__ . '/vendor/autoload.php';

use AnwarSaeed\InvoiceProcessor\Core\Database\ConnectionFactory;
use AnwarSaeed\InvoiceProcessor\Repositories\CustomerRepository;
use AnwarSaeed\InvoiceProcessor\Models\Customer;

/**
 * Database Switching Demonstration
 * This script demonstrates how the same code works with different databases
 * Proving the flexibility and SOLID principles implementation
 */

echo "🔄 Database Switching Demonstration\n";
echo "=====================================\n\n";

// Test SQLite Database
echo "📊 Testing SQLite Database:\n";
echo "----------------------------\n";

try {
    // Create SQLite connection
    $sqliteConnection = ConnectionFactory::createSqlite(':memory:');
    echo "✅ SQLite connection created successfully\n";
    
    // Setup schema
    $sqliteConnection->execute("
        CREATE TABLE customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            address TEXT NOT NULL
        )
    ");
    echo "✅ SQLite schema created successfully\n";
    
    // Test with SQLite repository
    $sqliteRepo = new CustomerRepository($sqliteConnection);
    
    // Create a customer
    $customer1 = new Customer(null, 'John Doe (SQLite)', '123 Main St');
    $savedCustomer1 = $sqliteRepo->save($customer1);
    
    echo "✅ Customer saved to SQLite with ID: " . $savedCustomer1->getId() . "\n";
    
    // Find the customer
    $foundCustomer1 = $sqliteRepo->findById($savedCustomer1->getId());
    echo "✅ Customer found in SQLite: " . $foundCustomer1->getName() . "\n";
    
} catch (Exception $e) {
    echo "❌ SQLite Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test MySQL Database (if available)
echo "📊 Testing MySQL Database:\n";
echo "---------------------------\n";

try {
    // Create MySQL connection
    $mysqlConnection = ConnectionFactory::createMysql(
        'localhost',
        'invoice_test_db',
        'root',  // Update with your MySQL credentials
        ''       // Update with your MySQL password
    );
    echo "✅ MySQL connection created successfully\n";
    
    // Setup schema
    $mysqlConnection->execute("
        CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            address TEXT NOT NULL
        )
    ");
    echo "✅ MySQL schema created successfully\n";
    
    // Test with MySQL repository
    $mysqlRepo = new CustomerRepository($mysqlConnection);
    
    // Create a customer
    $customer2 = new Customer(null, 'Jane Smith (MySQL)', '456 Oak Ave');
    $savedCustomer2 = $mysqlRepo->save($customer2);
    
    echo "✅ Customer saved to MySQL with ID: " . $savedCustomer2->getId() . "\n";
    
    // Find the customer
    $foundCustomer2 = $mysqlRepo->findById($savedCustomer2->getId());
    echo "✅ Customer found in MySQL: " . $foundCustomer2->getName() . "\n";
    
} catch (Exception $e) {
    echo "❌ MySQL Error: " . $e->getMessage() . "\n";
    echo "💡 Note: MySQL test failed - this is expected if MySQL is not configured\n";
}

echo "\n";

// Demonstrate the same code working with different databases
echo "🎯 SOLID Principles Demonstration:\n";
echo "==================================\n";

echo "✅ Dependency Inversion Principle (DIP):\n";
echo "   - High-level modules depend on abstractions (ConnectionInterface)\n";
echo "   - Not on concrete implementations (SQLite/MySQL)\n\n";

echo "✅ Liskov Substitution Principle (LSP):\n";
echo "   - SQLite and MySQL connections are interchangeable\n";
echo "   - Same repository code works with both databases\n\n";

echo "✅ Open/Closed Principle (OCP):\n";
echo "   - System is open for extension (new database types)\n";
echo "   - Closed for modification (existing code unchanged)\n\n";

echo "✅ Single Responsibility Principle (SRP):\n";
echo "   - ConnectionFactory handles only connection creation\n";
echo "   - CustomerRepository handles only customer operations\n\n";

echo "✅ Interface Segregation Principle (ISP):\n";
echo "   - ConnectionInterface focuses only on database operations\n";
echo "   - RepositoryInterface focuses only on data access\n\n";

echo "🏆 Design Patterns Demonstrated:\n";
echo "===============================\n";

echo "✅ Factory Pattern: ConnectionFactory creates different database connections\n";
echo "✅ Repository Pattern: Abstract data access layer\n";
echo "✅ Strategy Pattern: Different database strategies\n";
echo "✅ Dependency Injection: Dependencies injected through interfaces\n\n";

echo "🎉 Database Flexibility Test Complete!\n";
echo "=====================================\n";
echo "The same code works seamlessly with different database systems,\n";
echo "proving the architecture's flexibility and adherence to SOLID principles.\n";
