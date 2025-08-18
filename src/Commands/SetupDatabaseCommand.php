<?php

namespace AnwarSaeed\InvoiceProcessor\Commands;

use AnwarSaeed\InvoiceProcessor\Contracts\Commands\CommandInterface;
use AnwarSaeed\InvoiceProcessor\Core\DatabaseSetup;

/**
 * Setup Database Command
 * 
 * Automatically creates databases and tables based on .env configuration
 * Supports SQLite, MySQL, and MongoDB
 */
class SetupDatabaseCommand implements CommandInterface
{
    private DatabaseSetup $dbSetup;

    public function __construct()
    {
        $this->dbSetup = new DatabaseSetup();
    }

    public function execute(array $args = []): void
    {
        echo "🚀 Dynamic Database Setup\n";
        echo "========================\n\n";

        try {
            // Setup database based on current .env configuration
            $result = $this->dbSetup->setup();
            
            echo "\n✅ Database setup completed successfully!\n";
            echo "📊 Setup Summary:\n";
            echo "   Database Type: {$result['type']}\n";
            
            if (isset($result['path'])) {
                echo "   Database Path: {$result['path']}\n";
            }
            
            if (isset($result['database'])) {
                echo "   Database Name: {$result['database']}\n";
            }
            
            if (isset($result['tables'])) {
                echo "   Tables Created: " . implode(', ', $result['tables']) . "\n";
            }
            
            if (isset($result['collections'])) {
                echo "   Collections Ready: " . implode(', ', $result['collections']) . "\n";
            }
            
            // Show database statistics
            $stats = $this->dbSetup->getStats();
            echo "\n📈 Database Statistics:\n";
            foreach ($stats['tables'] as $table => $count) {
                echo "   {$table}: {$count} records\n";
            }
            
        } catch (\Exception $e) {
            echo "\n❌ Database setup failed: " . $e->getMessage() . "\n";
            echo "\n💡 Troubleshooting tips:\n";
            echo "   - Check your .env file configuration\n";
            echo "   - Ensure database server is running (for MySQL/MongoDB)\n";
            echo "   - Verify database credentials\n";
            echo "   - For MongoDB: Install MongoDB extension (composer require mongodb/mongodb)\n";
            exit(1);
        }
    }

    public function getName(): string
    {
        return 'setup-database';
    }

    public function getDescription(): string
    {
        return 'Setup database and tables based on .env configuration (supports SQLite, MySQL, MongoDB)';
    }
}
