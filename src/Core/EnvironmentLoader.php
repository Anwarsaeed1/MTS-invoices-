<?php

namespace AnwarSaeed\InvoiceProcessor\Core;

/**
 * Environment Loader
 * 
 * Loads environment variables from .env file
 */
class EnvironmentLoader
{
    private string $envFile;
    private array $loadedVars = [];

    public function __construct(string $envFile = '.env')
    {
        $this->envFile = $envFile;
        $this->load();
    }

    /**
     * Load environment variables from .env file
     */
    private function load(): void
    {
        if (!file_exists($this->envFile)) {
            return;
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
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                // Set environment variable
                $_ENV[$key] = $value;
                $this->loadedVars[$key] = $value;
            }
        }
    }

    /**
     * Get loaded environment variables
     */
    public function getLoadedVariables(): array
    {
        return $this->loadedVars;
    }

    /**
     * Get specific environment variable
     */
    public function get(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }

    /**
     * Set environment variable
     */
    public function set(string $key, $value): void
    {
        $_ENV[$key] = $value;
        $this->loadedVars[$key] = $value;
    }

    /**
     * Check if environment variable exists
     */
    public function has(string $key): bool
    {
        return isset($_ENV[$key]);
    }
}
