<?php

namespace AnwarSaeed\InvoiceProcessor\Commands;

use AnwarSaeed\InvoiceProcessor\Contracts\Commands\CommandInterface;
use AnwarSaeed\InvoiceProcessor\Core\DynamicEnvironmentLoader;

/**
 * Switch Database Command
 * 
 * Allows switching between different database types dynamically
 */
class SwitchDatabaseCommand implements CommandInterface
{
    private DynamicEnvironmentLoader $envLoader;

    public function __construct()
    {
        $this->envLoader = new DynamicEnvironmentLoader();
    }

    public function execute(array $args = []): void
    {
        $databaseType = $args['database'] ?? null;
        
        if (!$databaseType) {
            $this->showCurrentDatabase();
            $this->showAvailableOptions();
            return;
        }

        switch ($databaseType) {
            case 'sqlite':
                $this->switchToSqlite();
                break;
            case 'mysql':
                $this->switchToMysql();
                break;
            default:
                echo "❌ Unknown database type: {$databaseType}\n";
                echo "Available options: sqlite, mysql\n";
                break;
        }
    }

    private function showCurrentDatabase(): void
    {
        $currentType = $this->envLoader->getCurrentDatabaseType();
        $config = $this->envLoader->getDatabaseConfig();
        
        echo "📊 Current Database Configuration:\n";
        echo "   Type: " . strtoupper($currentType) . "\n";
        
        if ($currentType === 'mysql') {
            echo "   Host: " . $config['host'] . "\n";
            echo "   Database: " . $config['database'] . "\n";
            echo "   Username: " . $config['username'] . "\n";
        } else {
            echo "   Database: " . $config['database'] . "\n";
        }
        echo "\n";
    }

    private function showAvailableOptions(): void
    {
        echo "🔄 Available Database Options:\n";
        echo "   sqlite  - SQLite database (default)\n";
        echo "   mysql   - MySQL database\n";
        echo "\n";
        echo "Usage: php bin/console switch-database <database_type>\n";
        echo "Example: php bin/console switch-database mysql\n";
    }

    private function switchToSqlite(): void
    {
        echo "🔄 Switching to SQLite...\n";
        
        $this->envLoader->switchDatabase('sqlite', [
            'DB_DATABASE' => 'database/invoices.db'
        ]);
        
        echo "✅ Successfully switched to SQLite!\n";
        echo "   Database: database/invoices.db\n";
        echo "\n";
        echo "💡 Next steps:\n";
        echo "   1. Run: php database/setup.php\n";
        echo "   2. Run: php bin/console import data.xlsx\n";
        echo "   3. Test: http://localhost:8000/api/invoices\n";
    }

    private function switchToMysql(): void
    {
        echo "🔄 Switching to MySQL...\n";
        
        $this->envLoader->switchDatabase('mysql', [
            'DB_HOST' => 'localhost',
            'DB_DATABASE' => 'invoices',
            'DB_PORT' => '3306',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => ''
        ]);
        
        echo "✅ Successfully switched to MySQL!\n";
        echo "   Host: localhost\n";
        echo "   Database: invoices\n";
        echo "   Username: root\n";
        echo "\n";
        echo "💡 Next steps:\n";
        echo "   1. Run: php setup_mysql_database.php\n";
        echo "   2. Run: php bin/console import data.xlsx\n";
        echo "   3. Test: http://localhost:8000/api/invoices\n";
    }

    public function getDescription(): string
    {
        return 'Switch between different database types (sqlite, mysql)';
    }

    public function getName(): string
    {
        return 'switch-database';
    }
}
