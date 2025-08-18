<?php

namespace AnwarSaeed\InvoiceProcessor\Core;

use AnwarSaeed\InvoiceProcessor\Contracts\Database\DatabaseAdapterInterface;
use AnwarSaeed\InvoiceProcessor\Core\Database\ConnectionFactory;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\SqliteAdapter;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\MysqlAdapter;
use AnwarSaeed\InvoiceProcessor\Database\Adapters\MongoDbAdapter;
use AnwarSaeed\InvoiceProcessor\Core\EnvironmentLoader;

/**
 * Database Manager
 * 
 * This class handles database detection, configuration, and adapter creation.
 * It provides a centralized way to manage database connections and switching.
 */
class DatabaseManager
{
    private array $config;
    private string $currentDatabase;
    private ?DatabaseAdapterInterface $currentAdapter = null;

    public function __construct(array $config = [])
    {
        // Load environment variables
        new EnvironmentLoader();
        
        $this->config = $config ?: $this->loadDefaultConfig();
        $this->currentDatabase = $this->detectDatabaseType();
    }

    /**
     * Load default configuration
     */
    private function loadDefaultConfig(): array
    {
        $configFile = __DIR__ . '/../../config/database.php';
        
        if (file_exists($configFile)) {
            return require $configFile;
        }
        
        // Fallback configuration
        return [
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => __DIR__ . '/../../database/invoices.db',
                ],
                'mysql' => [
                    'driver' => 'mysql',
                    'host' => 'localhost',
                    'database' => 'invoices',
                    'username' => 'root',
                    'password' => '',
                ]
            ]
        ];
    }

    /**
     * Detect database type from various sources
     */
    private function detectDatabaseType(): string
    {
        // 1. Environment variable
        if (isset($_ENV['DB_TYPE'])) {
            return $_ENV['DB_TYPE'];
        }

        // 2. Command line argument
        global $argv;
        if (isset($argv)) {
            foreach ($argv as $arg) {
                if (strpos($arg, '--db=') === 0) {
                    return substr($arg, 5);
                }
            }
        }

        // 3. Configuration default
        return $this->config['default'] ?? 'sqlite';
    }

    /**
     * Get current database type
     */
    public function getCurrentDatabase(): string
    {
        return $this->currentDatabase;
    }

    /**
     * Get current database configuration
     */
    public function getCurrentConfig(): array
    {
        return $this->config['connections'][$this->currentDatabase] ?? [];
    }

    /**
     * Create adapter for current database
     */
    public function createAdapter(): DatabaseAdapterInterface
    {
        if ($this->currentAdapter === null) {
            $this->currentAdapter = $this->createAdapterForType($this->currentDatabase);
        }
        
        return $this->currentAdapter;
    }

    /**
     * Create adapter for specific database type
     */
    public function createAdapterForType(string $dbType): DatabaseAdapterInterface
    {
        $config = $this->config['connections'][$dbType] ?? [];
        
        return match ($dbType) {
            'sqlite' => $this->createSqliteAdapter($config),
            'mysql' => $this->createMysqlAdapter($config),
            'mongodb' => $this->createMongoDbAdapter($config),
            default => throw new \InvalidArgumentException("Unsupported database type: {$dbType}")
        };
    }

    /**
     * Switch to different database
     */
    public function switchDatabase(string $dbType): DatabaseAdapterInterface
    {
        if (!isset($this->config['connections'][$dbType])) {
            throw new \InvalidArgumentException("Database type '{$dbType}' not configured");
        }

        $this->currentDatabase = $dbType;
        $this->currentAdapter = $this->createAdapterForType($dbType);
        
        return $this->currentAdapter;
    }

    /**
     * Get available database types
     */
    public function getAvailableDatabases(): array
    {
        return array_keys($this->config['connections']);
    }

    /**
     * Check if database type is supported
     */
    public function isSupported(string $dbType): bool
    {
        return isset($this->config['connections'][$dbType]);
    }

    /**
     * Create SQLite adapter
     */
    private function createSqliteAdapter(array $config): DatabaseAdapterInterface
    {
        $database = $config['database'] ?? ':memory:';
        
        // If database is just a name, convert to full path
        if ($database === 'invoices' || !str_contains($database, '/') && !str_contains($database, '\\')) {
            $database = __DIR__ . '/../../database/' . $database . '.db';
        }
        
        $connection = ConnectionFactory::createSqlite($database);
        return new SqliteAdapter($connection);
    }

    /**
     * Create MySQL adapter
     */
    private function createMysqlAdapter(array $config): DatabaseAdapterInterface
    {
        $host = $config['host'] ?? 'localhost';
        $database = $config['database'] ?? 'invoices';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';
        
        $connection = ConnectionFactory::createMysql($host, $database, $username, $password);
        return new MysqlAdapter($connection);
    }

    /**
     * Create MongoDB adapter
     */
    private function createMongoDbAdapter(array $config): DatabaseAdapterInterface
    {
        if (!class_exists('MongoDB\Client')) {
            throw new \Exception("MongoDB extension not available. Install with: composer require mongodb/mongodb");
        }
        
        $host = $config['host'] ?? 'localhost';
        $database = $config['database'] ?? 'invoices';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        
        $dsn = "mongodb://{$host}/{$database}";
        $mongoClient = new \MongoDB\Client($dsn);
        return new MongoDbAdapter($mongoClient, $database, 'customers');
    }

    /**
     * Get database information
     */
    public function getDatabaseInfo(): array
    {
        return [
            'current' => $this->currentDatabase,
            'available' => $this->getAvailableDatabases(),
            'config' => $this->getCurrentConfig(),
            'adapter_class' => $this->currentAdapter ? get_class($this->currentAdapter) : null
        ];
    }
}
