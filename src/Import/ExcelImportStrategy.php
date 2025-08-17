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
        $reader = new RealExcelReader($filePath);
        
        // Validate the file structure
        $reader->validate();
        
        // Read the data
        $data = $reader->read();
        
        // Process and clean the data
        $processedData = [];
        foreach ($data as $row) {
            $processedData[] = [
                'invoice' => (int)$row['invoice'],
                'Invoice Date' => $this->convertExcelDate($row['Invoice Date']),
                'Customer Name' => trim($row['Customer Name']),
                'Customer Address' => trim($row['Customer Address']),
                'Product Name' => trim($row['Product Name']),
                'Quantity' => (int)($row['Quantity'] ?? $row['Qyantity'] ?? 0),
                'Price' => (float)$row['Price'],
                'Total' => (float)$row['Total'],
                'Grand Total' => (float)$row['Grand Total']
            ];
        }
        
        return $processedData;
    }
    
    /**
     * Convert Excel date to PHP date
     */
    private function convertExcelDate($excelDate): string
    {
        if (is_numeric($excelDate)) {
            // Excel dates are number of days since 1900-01-01
            $unixTimestamp = ($excelDate - 25569) * 86400;
            return date('Y-m-d', $unixTimestamp);
        }
        
        // If it's already a string date, return as is
        return $excelDate;
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
