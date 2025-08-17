<?php

namespace AnwarSaeed\InvoiceProcessor\Commands;

use AnwarSaeed\InvoiceProcessor\Contracts\Services\InvoiceServiceInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Commands\CommandInterface;

class ImportCommand implements CommandInterface
{
    private InvoiceServiceInterface $invoiceService;

    public function __construct(InvoiceServiceInterface $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function execute(array $args = []): void
    {
        if (count($args) < 1) {
            echo "Usage: php bin/console import <excel_file_path>\n";
            echo "Example: php bin/console import data.xlsx\n";
            exit(1);
        }

        $filePath = $args[0];
        
        if (!file_exists($filePath)) {
            echo "Error: File '$filePath' not found.\n";
            exit(1);
        }

        try {
            echo "Starting import from: $filePath\n";
            
            $result = $this->invoiceService->importFromFile($filePath);
            
            echo "Import completed successfully!\n";
            echo "Imported {$result['invoices']} invoices, {$result['customers']} customers, {$result['products']} products, {$result['items']} items.\n";
            
        } catch (\Exception $e) {
            echo "Error during import: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function getName(): string
    {
        return 'import';
    }
    
    public function getDescription(): string
    {
        return 'Import invoices from Excel file';
    }
} 