<?php
declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Repositories\{
    InvoiceRepository,
    CustomerRepository,
    ProductRepository
};
use AnwarSaeed\InvoiceProcessor\Export\JsonRenderer;

// Get format from query parameter
$format = $_GET['format'] ?? 'json';

if (!in_array($format, ['json', 'xml'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid format. Use json or xml.']);
    exit;
}

try {
    // Initialize database connection
    $connection = new Connection("sqlite:" . __DIR__ . "/../database/invoices.db");
    
    $invoiceRepo = new InvoiceRepository($connection);
    $customerRepo = new CustomerRepository($connection);
    $productRepo = new ProductRepository($connection);
    
    // Get all invoices
    $invoices = $invoiceRepo->paginate(1, 1000)['data'];
    $result = [];
    
    foreach ($invoices as $invoice) {
        $items = $invoiceRepo->getItems($invoice['id']);
        $customer = $customerRepo->findById($invoice['customer_id']);
        
        $result[] = [
            'invoice' => $invoice,
            'customer' => $customer ? [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'address' => $customer->getAddress()
            ] : null,
            'items' => $items
        ];
    }
    
    // Export based on format
    if ($format === 'json') {
        header('Content-Type: application/json');
        $renderer = new JsonRenderer();
        echo $renderer->renderInvoices($result);
    } else {
        header('Content-Type: application/xml');
        
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><invoices></invoices>');
        
        foreach ($result as $invoiceData) {
            $invoice = $xml->addChild('invoice');
            $invoice->addChild('id', $invoiceData['invoice']['id']);
            $invoice->addChild('date', $invoiceData['invoice']['invoice_date']);
            $invoice->addChild('grand_total', $invoiceData['invoice']['grand_total']);
            
            if ($invoiceData['customer']) {
                $customer = $invoice->addChild('customer');
                $customer->addChild('id', $invoiceData['customer']['id']);
                $customer->addChild('name', $invoiceData['customer']['name']);
                $customer->addChild('address', $invoiceData['customer']['address']);
            }
            
            $items = $invoice->addChild('items');
            foreach ($invoiceData['items'] as $item) {
                $itemNode = $items->addChild('item');
                $itemNode->addChild('product_name', $item['product_name']);
                $itemNode->addChild('quantity', $item['quantity']);
                $itemNode->addChild('total', $item['total']);
            }
        }
        
        echo $xml->asXML();
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Export failed: ' . $e->getMessage()]);
} 