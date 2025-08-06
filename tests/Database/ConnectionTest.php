<?php
namespace AnwarSaeed\InvoiceProcessor\Tests\Database;

use AnwarSaeed\InvoiceProcessor\Database\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testConnection()
    {
        $connection = new Connection('sqlite::memory:');
        $this->assertInstanceOf(\PDO::class, $connection->getPdo());
    }
    
    public function testExecuteQuery()
    {
        $connection = new Connection('sqlite::memory:');
        $connection->execute("CREATE TABLE test (id INTEGER)");
        $connection->execute("INSERT INTO test VALUES (1)");
        
        $stmt = $connection->execute("SELECT id FROM test");
        $result = $stmt->fetch();
        
        $this->assertEquals(1, $result['id']);
    }
}