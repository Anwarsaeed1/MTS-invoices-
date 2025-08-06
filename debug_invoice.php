<?php
require __DIR__ . '/vendor/autoload.php';

use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Repositories\{
    InvoiceRepository,
    CustomerRepository,
    ProductRepository
};
use AnwarSaeed\InvoiceProcessor\Services\InvoiceService;

echo "Testing Invoice Details...\n";

try {
    $connection = new Connection("sqlite:" . __DIR__ . "/database/invoices.db");
    $invoiceRepo = new InvoiceRepository($connection);
    $customerRepo = new CustomerRepository($connection);
    $productRepo = new ProductRepository($connection);
    
    $invoiceService = new InvoiceService($invoiceRepo, $customerRepo, $productRepo);
    
    // Test findById directly
    echo "Testing findById(1):\n";
    $invoice = $invoiceRepo->findById(1);
    var_dump($invoice);
    
    if ($invoice) {
        echo "Invoice found! customer_id: " . $invoice['customer_id'] . "\n";
        
        // Test customer lookup
        echo "Testing customer lookup:\n";
        $customer = $customerRepo->findById($invoice['customer_id']);
        if ($customer) {
            echo "Customer found: " . $customer->getName() . "\n";
        } else {
            echo "Customer not found!\n";
        }
        
        // Test getInvoiceDetails
        echo "Testing getInvoiceDetails(1):\n";
        $details = $invoiceService->getInvoiceDetails(1);
        echo "Success! Invoice details:\n";
        print_r($details);
        
    } else {
        echo "Invoice not found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 