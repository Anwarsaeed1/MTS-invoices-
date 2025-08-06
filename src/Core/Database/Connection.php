<?php
namespace AnwarSaeed\InvoiceProcessor\Core\Database;

class Connection
{
    private \PDO $pdo;
    
    public function __construct(string $dsn, ?string $username = null, ?string $password = null, array $options = [])
    {
        $defaultOptions = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ];
        $this->pdo = new \PDO($dsn, $username, $password, array_merge($defaultOptions, $options));
    }
    
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
    
    public function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}