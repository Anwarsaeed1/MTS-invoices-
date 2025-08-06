<?php
namespace AnwarSaeed\InvoiceProcessor\Database;

class Connection
{
    private \PDO $pdo;
    

    /**
     * Initializes a new database connection using the provided DSN, username, password, and options.
     *
     * @param string $dsn The Data Source Name, or DSN, containing the information required to connect to the database.
     * @param string|null $username The username for the DSN string. Default is null.
     * @param string|null $password The password for the DSN string. Default is null.
     * @param array $options An array of options for the PDO connection. Default is an empty array.
     */

    public function __construct(string $dsn, ?string $username = null, ?string $password = null, array $options = [])
    {
        // src/Database/Connection.php
         $defaultOptions = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, // This is correct
             \PDO::ATTR_EMULATE_PREPARES => false
        ];
        $this->pdo = new \PDO($dsn, $username, $password, array_merge($defaultOptions, $options));
    }
  

    /**
     * Retrieves the PDO instance used by the connection.
     *
     * @return \PDO The PDO instance.
     */

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
    
    /**
     * Prepares and executes an SQL statement.
     *
     * @param string $sql The SQL statement to be prepared and executed.
     * @param array $params An optional array of parameters to bind to the SQL statement.
     * @return \PDOStatement The resulting PDOStatement object.
     */

    public function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}