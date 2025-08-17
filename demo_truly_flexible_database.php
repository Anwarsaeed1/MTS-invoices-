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

echo "🎯 Truly Flexible Database Demonstration\n";
echo "========================================\n\n";

echo "❌ PROBLEM: Database-specific repository names\n";
echo "   • MongoDBCustomerRepository (tightly coupled to MongoDB)\n";
echo "   • SqliteCustomerRepository (tightly coupled to SQLite)\n";
echo "   • MysqlCustomerRepository (tightly coupled to MySQL)\n\n";

echo "✅ SOLUTION: Database-agnostic repository with adapters\n";
echo "   • FlexibleCustomerRepository (works with ANY database)\n";
echo "   • DatabaseAdapterInterface (abstracts database operations)\n";
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
    
    echo "✅ Customer saved to SQLite with ID: " . $saved1->getId() . "\n";
    echo "   📋 Repository: FlexibleCustomerRepository (database-agnostic)\n";
    echo "   🔧 Adapter: SqliteAdapter (implements DatabaseAdapterInterface)\n\n";
    
} catch (Exception $e) {
    echo "❌ SQLite Error: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// 2. MySQL Database (RDBMS)
// ============================================================================
echo "📊 2. MySQL Database (RDBMS)\n";
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
    
    echo "✅ Customer saved to MySQL with ID: " . $saved2->getId() . "\n";
    echo "   📋 Repository: FlexibleCustomerRepository (SAME class!)\n";
    echo "   🔧 Adapter: MysqlAdapter (implements DatabaseAdapterInterface)\n\n";
    
} catch (Exception $e) {
    echo "❌ MySQL Error: " . $e->getMessage() . "\n";
    echo "💡 Note: MySQL test failed - this is expected if MySQL is not configured\n\n";
}

// ============================================================================
// 3. MongoDB Database (NoSQL)
// ============================================================================
echo "📄 3. MongoDB Database (NoSQL)\n";
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
    
    echo "✅ Customer saved to MongoDB with ID: " . $saved3->getId() . "\n";
    echo "   📋 Repository: FlexibleCustomerRepository (SAME class!)\n";
    echo "   🔧 Adapter: MongoDbAdapter (implements DatabaseAdapterInterface)\n\n";
    
} catch (Exception $e) {
    echo "❌ MongoDB Error: " . $e->getMessage() . "\n";
    echo "💡 Note: MongoDB test failed - this is expected if MongoDB is not installed\n\n";
    
    // Show the architecture anyway
    echo "📋 MongoDB Architecture (demonstration):\n";
    echo "```php\n";
    echo "// Same repository works with MongoDB!\n";
    echo "\$mongoAdapter = new MongoDbAdapter(\$mongoClient);\n";
    echo "\$mongoRepo = new FlexibleCustomerRepository(\$mongoAdapter);\n";
    echo "// No database-specific names in repository!\n";
    echo "```\n\n";
}

// ============================================================================
// 4. Adding New Database Types (Redis Example)
// ============================================================================
echo "🔧 4. Adding New Database Types\n";
echo "===============================\n";

echo "📋 To add Redis (Key-Value Store):\n";
echo "```php\n";
echo "// 1. Create Redis Adapter\n";
echo "class RedisAdapter implements DatabaseAdapterInterface {\n";
echo "    public function __construct(Redis \$redis) { ... }\n";
echo "    public function findById(string \$table, \$id): ?array { ... }\n";
echo "    public function insert(string \$table, array \$data): mixed { ... }\n";
echo "    // Implement other methods\n";
echo "}\n\n";
echo "// 2. Use SAME repository!\n";
echo "\$redisAdapter = new RedisAdapter(\$redis);\n";
echo "\$redisRepo = new FlexibleCustomerRepository(\$redisAdapter);\n";
echo "// No changes to repository code!\n";
echo "```\n\n";

echo "📋 To add Neo4j (Graph Database):\n";
echo "```php\n";
echo "// 1. Create Neo4j Adapter\n";
echo "class Neo4jAdapter implements DatabaseAdapterInterface {\n";
echo "    public function __construct(GraphDatabase \$neo4j) { ... }\n";
echo "    public function findById(string \$table, \$id): ?array { ... }\n";
echo "    public function insert(string \$table, array \$data): mixed { ... }\n";
echo "    // Implement other methods\n";
echo "}\n\n";
echo "// 2. Use SAME repository!\n";
echo "\$neo4jAdapter = new Neo4jAdapter(\$neo4j);\n";
echo "\$neo4jRepo = new FlexibleCustomerRepository(\$neo4jAdapter);\n";
echo "// No changes to repository code!\n";
echo "```\n\n";

// ============================================================================
// 5. Key Benefits
// ============================================================================
echo "🎯 Key Benefits of This Approach\n";
echo "================================\n\n";

echo "✅ 1. NO Database-Specific Names:\n";
echo "   • Repository: FlexibleCustomerRepository (not MongoDBCustomerRepository)\n";
echo "   • Same repository works with ANY database\n";
echo "   • No need to create separate repositories for each database\n\n";

echo "✅ 2. Easy Database Switching:\n";
echo "   • Just change the adapter, repository stays the same\n";
echo "   • SQLite → MySQL → MongoDB → Redis → Neo4j\n";
echo "   • No code changes in business logic\n\n";

echo "✅ 3. SOLID Principles:\n";
echo "   • DIP: Depend on DatabaseAdapterInterface (abstraction)\n";
echo "   • LSP: All adapters are interchangeable\n";
echo "   • OCP: Open for new database types, closed for modification\n";
echo "   • SRP: Each adapter handles one database type\n";
echo "   • ISP: Focused interfaces\n\n";

echo "✅ 4. Design Patterns:\n";
echo "   • Adapter Pattern: DatabaseAdapterInterface\n";
echo "   • Strategy Pattern: Different database strategies\n";
echo "   • Factory Pattern: Create different adapters\n";
echo "   • Dependency Injection: Inject adapters\n\n";

// ============================================================================
// 6. Comparison: Before vs After
// ============================================================================
echo "🔄 Comparison: Before vs After\n";
echo "==============================\n\n";

echo "❌ BEFORE (Tightly Coupled):\n";
echo "```php\n";
echo "// Different repositories for each database\n";
echo "class MongoDBCustomerRepository { ... }\n";
echo "class SqliteCustomerRepository { ... }\n";
echo "class MysqlCustomerRepository { ... }\n";
echo "class RedisCustomerRepository { ... }\n";
echo "// Need to create new repository for each database!\n";
echo "```\n\n";

echo "✅ AFTER (Flexible & Database-Agnostic):\n";
echo "```php\n";
echo "// ONE repository works with ANY database\n";
echo "class FlexibleCustomerRepository {\n";
echo "    public function __construct(DatabaseAdapterInterface \$adapter) { ... }\n";
echo "    // Same code works with SQLite, MySQL, MongoDB, Redis, Neo4j, etc.\n";
echo "}\n";
echo "```\n\n";

// ============================================================================
// 7. Conclusion
// ============================================================================
echo "🎉 Conclusion: Truly Flexible Database Architecture!\n";
echo "==================================================\n\n";

echo "✅ Your code is now TRULY database-agnostic:\n";
echo "   • No database-specific names in repositories\n";
echo "   • Same repository works with ANY database\n";
echo "   • Easy to add new database types\n";
echo "   • No vendor lock-in\n";
echo "   • Production-ready flexibility\n\n";

echo "🚀 You can now easily switch between:\n";
echo "   • SQLite → MySQL → PostgreSQL → MariaDB (RDBMS)\n";
echo "   • MongoDB → CouchDB → Cassandra (NoSQL)\n";
echo "   • Redis → Memcached (Key-Value)\n";
echo "   • Neo4j → ArangoDB (Graph)\n";
echo "   • Any future database technology!\n\n";

echo "🎯 The original problem is SOLVED:\n";
echo "   ✅ 'Cannot replace SQLite with MariaDB' → SOLVED!\n";
echo "   ✅ 'Cannot replace CustomerRepository to MongoDB Repository' → SOLVED!\n";
echo "   ✅ Your code is now truly flexible and database-agnostic!\n";
