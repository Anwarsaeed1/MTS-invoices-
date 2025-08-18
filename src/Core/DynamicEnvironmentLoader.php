<?php

namespace AnwarSaeed\InvoiceProcessor\Core;

/**
 * Dynamic Environment Loader
 * 
 * Loads environment variables from .env file and applies them dynamically
 */
class DynamicEnvironmentLoader
{
    private string $envFile;
    private array $loadedVars = [];

    public function __construct(string $envFile = null)
    {
        $this->envFile = $envFile ?: __DIR__ . '/../../.env';
        $this->loadEnvironment();
    }

    /**
     * Load environment variables from .env file
     */
    public function loadEnvironment(): void
    {
        if (!file_exists($this->envFile)) {
            // Create default .env file if it doesn't exist
            $this->createDefaultEnvFile();
        }

        $lines = file($this->envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '"\'');
                
                // Set environment variable
                $_ENV[$key] = $value;
                $this->loadedVars[$key] = $value;
            }
        }
    }

    /**
     * Create default .env file with SQLite configuration
     */
    private function createDefaultEnvFile(): void
    {
        $defaultEnv = "# Database Configuration\n";
        $defaultEnv .= "DB_TYPE=sqlite\n";
        $defaultEnv .= "DB_DATABASE=database/invoices.db\n";
        $defaultEnv .= "\n";
        $defaultEnv .= "# MySQL Configuration (uncomment to use)\n";
        $defaultEnv .= "# DB_TYPE=mysql\n";
        $defaultEnv .= "# DB_HOST=localhost\n";
        $defaultEnv .= "# DB_DATABASE=invoices\n";
        $defaultEnv .= "# DB_PORT=3306\n";
        $defaultEnv .= "# DB_USERNAME=root\n";
        $defaultEnv .= "# DB_PASSWORD=\n";
        $defaultEnv .= "\n";
        $defaultEnv .= "# Application Configuration\n";
        $defaultEnv .= "APP_ENV=development\n";
        $defaultEnv .= "APP_DEBUG=true\n";

        file_put_contents($this->envFile, $defaultEnv);
    }

    /**
     * Reload environment variables (useful for dynamic switching)
     */
    public function reloadEnvironment(): void
    {
        $this->loadEnvironment();
    }

    /**
     * Get current database type
     */
    public function getCurrentDatabaseType(): string
    {
        return $_ENV['DB_TYPE'] ?? 'sqlite';
    }

    /**
     * Get all loaded environment variables
     */
    public function getLoadedVariables(): array
    {
        return $this->loadedVars;
    }

    /**
     * Check if environment has changed and reload if necessary
     */
    public function checkForChanges(): bool
    {
        $currentHash = md5_file($this->envFile);
        $storedHash = $_ENV['ENV_FILE_HASH'] ?? '';
        
        if ($currentHash !== $storedHash) {
            $_ENV['ENV_FILE_HASH'] = $currentHash;
            $this->reloadEnvironment();
            return true;
        }
        
        return false;
    }

    /**
     * Switch database type dynamically
     */
    public function switchDatabase(string $type, array $config = []): void
    {
        $envContent = file_get_contents($this->envFile);
        
        // Update DB_TYPE
        $envContent = preg_replace('/^DB_TYPE=.*$/m', "DB_TYPE={$type}", $envContent);
        
        // Update specific database configuration
        foreach ($config as $key => $value) {
            $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
        }
        
        file_put_contents($this->envFile, $envContent);
        $this->reloadEnvironment();
    }

    /**
     * Get database configuration for current type
     */
    public function getDatabaseConfig(): array
    {
        $type = $this->getCurrentDatabaseType();
        
        switch ($type) {
            case 'mysql':
                return [
                    'driver' => 'mysql',
                    'host' => $_ENV['DB_HOST'] ?? 'localhost',
                    'port' => $_ENV['DB_PORT'] ?? '3306',
                    'database' => $_ENV['DB_DATABASE'] ?? 'invoices',
                    'username' => $_ENV['DB_USERNAME'] ?? 'root',
                    'password' => $_ENV['DB_PASSWORD'] ?? '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ];
            
            case 'sqlite':
                return [
                    'driver' => 'sqlite',
                    'database' => $_ENV['DB_DATABASE'] ?? __DIR__ . '/../../database/invoices.db',
                ];
            
            default:
                return [
                    'driver' => 'sqlite',
                    'database' => __DIR__ . '/../../database/invoices.db',
                ];
        }
    }
}
