<?php

namespace AnwarSaeed\InvoiceProcessor\Commands;

use AnwarSaeed\InvoiceProcessor\Services\ImportService;
use AnwarSaeed\InvoiceProcessor\Database\Connection;

class ImportCommand
{
    private ImportService $importService;

    public function __construct()
    {
        $connection = new Connection("sqlite:" . __DIR__ . "/../../database/invoices.db");
        
        $customerRepo = new \AnwarSaeed\InvoiceProcessor\Repositories\CustomerRepository($connection);
        $productRepo = new \AnwarSaeed\InvoiceProcessor\Repositories\ProductRepository($connection);
        $invoiceRepo = new \AnwarSaeed\InvoiceProcessor\Repositories\InvoiceRepository($connection);
        
        $this->importService = new ImportService($customerRepo, $productRepo, $invoiceRepo);
    }

    public function execute(array $args): void
    {
        if (count($args) < 2) {
            echo "Usage: php bin/console import <excel_file_path>\n";
            echo "Example: php bin/console import data.xlsx\n";
            exit(1);
        }

        $filePath = $args[1];
        
        if (!file_exists($filePath)) {
            echo "Error: File '$filePath' not found.\n";
            exit(1);
        }

        try {
            echo "Starting import from: $filePath\n";
            
            $data = $this->parseExcelFile($filePath);
            $result = $this->importService->importFromArray($data);
            
            echo "Import completed successfully!\n";
            echo "Imported " . count($result) . " invoices.\n";
            
        } catch (\Exception $e) {
            echo "Error during import: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function parseExcelFile(string $filePath): array
    {
        // Mock implementation - in real scenario, use PhpSpreadsheet or Spout
        return [
            [
                'invoice' => 1,
                'Invoice Date' => 43831,
                'Customer Name' => 'John Doe',
                'Customer Address' => '123 Main St, City',
                'Product Name' => 'Product A',
                'Qyantity' => 2,
                'Price' => 10.50,
                'Total' => 21.00,
                'Grand Total' => 21.00
            ],
            [
                'invoice' => 1,
                'Invoice Date' => 43831,
                'Customer Name' => 'John Doe',
                'Customer Address' => '123 Main St, City',
                'Product Name' => 'Product B',
                'Qyantity' => 1,
                'Price' => 15.00,
                'Total' => 15.00,
                'Grand Total' => 36.00
            ],
            [
                'invoice' => 2,
                'Invoice Date' => 43832,
                'Customer Name' => 'Jane Smith',
                'Customer Address' => '456 Oak Ave, Town',
                'Product Name' => 'Product C',
                'Qyantity' => 3,
                'Price' => 8.00,
                'Total' => 24.00,
                'Grand Total' => 24.00
            ],
            [
                'invoice' => 3,
                'Invoice Date' => 43833,
                'Customer Name' => 'Bob Johnson',
                'Customer Address' => '789 Pine Rd, Village',
                'Product Name' => 'Product D',
                'Qyantity' => 1,
                'Price' => 25.00,
                'Total' => 25.00,
                'Grand Total' => 25.00
            ]
        ];
    }
} 