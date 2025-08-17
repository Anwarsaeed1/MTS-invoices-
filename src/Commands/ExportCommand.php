<?php

namespace AnwarSaeed\InvoiceProcessor\Commands;

use AnwarSaeed\InvoiceProcessor\Contracts\Services\InvoiceServiceInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Commands\CommandInterface;

class ExportCommand implements CommandInterface
{
    private InvoiceServiceInterface $invoiceService;

    public function __construct(InvoiceServiceInterface $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function execute(array $args = []): void
    {
        $format = $args[0] ?? 'json';
        
        if (!in_array($format, ['json', 'xml'])) {
            echo "Error: Invalid format. Use 'json' or 'xml'.\n";
            exit(1);
        }

        try {
            $exportedData = $this->invoiceService->exportInvoices($format);
            echo $exportedData;
            
        } catch (\Exception $e) {
            echo "Error during export: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function getName(): string
    {
        return 'export';
    }
    
    public function getDescription(): string
    {
        return 'Export invoices to JSON or XML format';
    }
} 