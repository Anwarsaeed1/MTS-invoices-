<?php
require __DIR__ . '/vendor/autoload.php';

use AnwarSaeed\InvoiceProcessor\Database\Connection;

echo "Checking database contents...\n";

try {
    $connection = new Connection("sqlite:" . __DIR__ . "/database/invoices.db");
    
    // Check invoices
    echo "Invoices:\n";
    $invoices = $connection->execute("SELECT * FROM invoices")->fetchAll();
    print_r($invoices);
    
    // Check customers
    echo "\nCustomers:\n";
    $customers = $connection->execute("SELECT * FROM customers")->fetchAll();
    print_r($customers);
    
    // Check products
    echo "\nProducts:\n";
    $products = $connection->execute("SELECT * FROM products")->fetchAll();
    print_r($products);
    
    // Check invoice_items
    echo "\nInvoice Items:\n";
    $items = $connection->execute("SELECT * FROM invoice_items")->fetchAll();
    print_r($items);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 