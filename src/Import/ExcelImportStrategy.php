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
        // Mock implementation - replace with real Excel reader like PhpSpreadsheet
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
            ]
        ];
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
