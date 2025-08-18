<?php

namespace AnwarSaeed\InvoiceProcessor\Import;

use AnwarSaeed\InvoiceProcessor\Contracts\Import\ImportStrategyInterface;

class ExcelImportStrategy implements ImportStrategyInterface
{
    /**
     * Import data from Excel file.
     *
     * @param string $filePath The path to the Excel file.
     * @return array An array of associative arrays representing the data in the Excel file.
     */
    public function import(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Use CSV reader for CSV files (when ZipArchive is not available)
        if ($extension === 'csv') {
            $reader = new SimpleCsvReader();
        } else {
            $reader = new PhpSpreadsheetReader();
        }
        
        // Read the data
        $data = $reader->read($filePath);
        
        // Process and clean the data
        $processedData = [];
        foreach ($data as $row) {
            $processedData[] = [
                'invoice' => (int)($row['invoice'] ?? 0),
                'Invoice Date' => $row['invoice_date'] ?? '',
                'Customer Name' => trim($row['customer_name'] ?? ''),
                'Customer Address' => trim($row['customer_address'] ?? ''),
                'Product Name' => trim($row['product_name'] ?? ''),
                'Quantity' => (int)($row['quantity'] ?? $row['qyantity'] ?? 0), // Handle typo
                'Price' => (float)($row['price'] ?? 0),
                'Total' => (float)($row['total'] ?? 0),
                'Grand Total' => (float)($row['grand_total'] ?? 0)
            ];
        }
        
        return $processedData;
    }
    

    
    /**
     * Check if the strategy can handle the given file.
     *
     * @param string $filePath The path to the file to check.
     * @return bool True if the strategy can handle the file, false otherwise.
     */
    public function canHandle(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, $this->getSupportedExtensions());
    }
    
    /**
     * Get the file extensions supported by this strategy.
     *
     * @return array An array of file extensions supported by this strategy.
     */
    public function getSupportedExtensions(): array
    {
        return ['xlsx', 'xls', 'csv'];
    }
}
