<?php

namespace AnwarSaeed\InvoiceProcessor\Import;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use InvalidArgumentException;
use Exception;

/**
 * Professional Excel Reader using PhpSpreadsheet library
 * 
 * This class provides robust Excel file reading capabilities using the PhpSpreadsheet library.
 * It supports .xlsx, .xls, .csv, and other Excel formats with proper error handling.
 */
class PhpSpreadsheetReader
{
    /**
     * Read Excel file and return data as array
     *
     * @param string $filePath Path to the Excel file
     * @return array Array of data with headers as keys
     * @throws InvalidArgumentException If file doesn't exist or is invalid
     * @throws Exception If reading fails
     */
    public function read(string $filePath): array
    {
        // Validate file exists
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        // Validate file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls', 'csv', 'ods'])) {
            throw new InvalidArgumentException("Unsupported file format: {$extension}");
        }

        try {
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($filePath);
            
            // Get the first worksheet
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Read data from the worksheet
            $data = $this->readWorksheet($worksheet);
            
            // Close the spreadsheet to free memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            return $data;
            
        } catch (Exception $e) {
            throw new Exception("Failed to read Excel file: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Read data from a worksheet
     *
     * @param Worksheet $worksheet The worksheet to read
     * @return array Array of data with headers as keys
     */
    private function readWorksheet(Worksheet $worksheet): array
    {
        // Get the highest row and column numbers
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        // Read headers from first row
        $headers = $this->readHeaders($worksheet, $highestColumnIndex);
        
        // Validate headers
        $this->validateHeaders($headers);

        // Read data rows
        $data = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $this->readRow($worksheet, $row, $highestColumnIndex, $headers);
            
            // Skip empty rows
            if (!empty(array_filter($rowData))) {
                $data[] = $rowData;
            }
        }

        return $data;
    }

    /**
     * Read headers from the first row
     *
     * @param Worksheet $worksheet The worksheet
     * @param int $highestColumnIndex Highest column index
     * @return array Array of headers
     */
    private function readHeaders(Worksheet $worksheet, int $highestColumnIndex): array
    {
        $headers = [];
        
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellCoordinate = Coordinate::stringFromColumnIndex($col) . '1';
            $cellValue = $worksheet->getCell($cellCoordinate)->getValue();
            $headers[$col] = $this->normalizeHeader($cellValue);
        }

        return $headers;
    }

    /**
     * Read a data row
     *
     * @param Worksheet $worksheet The worksheet
     * @param int $row Row number
     * @param int $highestColumnIndex Highest column index
     * @param array $headers Headers array
     * @return array Row data with headers as keys
     */
    private function readRow(Worksheet $worksheet, int $row, int $highestColumnIndex, array $headers): array
    {
        $rowData = [];
        
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellCoordinate = Coordinate::stringFromColumnIndex($col) . $row;
            $cell = $worksheet->getCell($cellCoordinate);
            $value = $this->getCellValue($cell);
            
            $header = $headers[$col];
            $rowData[$header] = $value;
        }

        return $rowData;
    }

    /**
     * Get formatted cell value
     *
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell $cell The cell
     * @return mixed Formatted cell value
     */
    private function getCellValue($cell)
    {
        $value = $cell->getValue();
        
        // Handle date values
        if ($cell->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC) {
            if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                $value = $this->formatDate($cell->getCalculatedValue());
            }
        }
        
        // Handle empty values
        if ($value === null || $value === '') {
            return '';
        }
        
        return $value;
    }

    /**
     * Format date from Excel timestamp
     *
     * @param float $excelDate Excel date value
     * @return string Formatted date string (Y-m-d)
     */
    private function formatDate(float $excelDate): string
    {
        try {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate);
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            // Return original value if date conversion fails
            return (string) $excelDate;
        }
    }

    /**
     * Normalize header name
     *
     * @param mixed $header Raw header value
     * @return string Normalized header
     */
    private function normalizeHeader($header): string
    {
        if ($header === null || $header === '') {
            return 'column_' . uniqid();
        }
        
        $header = (string) $header;
        $header = trim($header);
        $header = strtolower($header);
        $header = preg_replace('/[^a-z0-9_]/', '_', $header);
        $header = preg_replace('/_+/', '_', $header);
        $header = trim($header, '_');
        
        return $header ?: 'column_' . uniqid();
    }

    /**
     * Validate required headers
     *
     * @param array $headers Headers array
     * @throws InvalidArgumentException If required headers are missing
     */
    private function validateHeaders(array $headers): void
    {
        // Check for required headers with flexible naming
        $requiredHeaders = [
            'invoice_date' => ['invoice_date', 'invoice date', 'date'],
            'customer_name' => ['customer_name', 'customer name', 'name'],
            'customer_address' => ['customer_address', 'customer address', 'address'],
            'product_name' => ['product_name', 'product name', 'product'],
            'quantity' => ['quantity', 'qyantity', 'qty'], // Handle typo
            'price' => ['price', 'unit_price', 'unit price']
        ];
        
        $missingHeaders = [];
        
        foreach ($requiredHeaders as $required => $variations) {
            $found = false;
            foreach ($variations as $variation) {
                if (in_array($variation, $headers)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingHeaders[] = $required;
            }
        }
        
        if (!empty($missingHeaders)) {
            throw new InvalidArgumentException(
                "Missing required headers: " . implode(', ', $missingHeaders) . 
                ". Available headers: " . implode(', ', $headers)
            );
        }
    }

    /**
     * Get supported file extensions
     *
     * @return array Array of supported extensions
     */
    public function getSupportedExtensions(): array
    {
        return ['xlsx', 'xls', 'csv', 'ods'];
    }

    /**
     * Check if file can be handled
     *
     * @param string $filePath Path to the file
     * @return bool True if file can be handled
     */
    public function canHandle(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, $this->getSupportedExtensions());
    }
}
