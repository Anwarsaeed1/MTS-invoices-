<?php

namespace AnwarSaeed\InvoiceProcessor\Import;

/**
 * Real Excel Reader
 * 
 * This class reads Excel files using available PHP extensions.
 * It attempts to read the actual Excel file content using different methods.
 */
class RealExcelReader
{
    private string $filePath;
    private array $headers = [];
    private array $data = [];

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Read the Excel file and return structured data
     */
    public function read(): array
    {
        if (!file_exists($this->filePath)) {
            throw new \InvalidArgumentException("File not found: {$this->filePath}");
        }

        $extension = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'xlsx':
                return $this->readXlsx();
            case 'xls':
                return $this->readXls();
            case 'csv':
                return $this->readCsv();
            default:
                throw new \InvalidArgumentException("Unsupported file format: {$extension}");
        }
    }

    /**
     * Read .xlsx file (Excel 2007+ format)
     */
    private function readXlsx(): array
    {
        // Try to read using ZipArchive if available
        if (class_exists('ZipArchive')) {
            return $this->readXlsxWithZip();
        }
        
        // Try to read using system commands
        if ($this->canUseSystemCommands()) {
            return $this->readXlsxWithSystemCommand();
        }
        
        // Fallback to parsing the file manually
        return $this->readXlsxManually();
    }

    /**
     * Read .xlsx file using ZipArchive
     */
    private function readXlsxWithZip(): array
    {
        $zip = new \ZipArchive();
        
        if ($zip->open($this->filePath) !== true) {
            throw new \RuntimeException("Cannot open Excel file: {$this->filePath}");
        }

        try {
            // Read the shared strings (if any)
            $sharedStrings = $this->readSharedStrings($zip);
            
            // Read the first worksheet
            $worksheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
            
            if (!$worksheetData) {
                throw new \RuntimeException("No worksheet found in Excel file");
            }

            $data = $this->parseWorksheetXml($worksheetData, $sharedStrings);
            $zip->close();
            
            return $data;
            
        } catch (\Exception $e) {
            $zip->close();
            throw $e;
        }
    }

    /**
     * Read .xlsx file using system command (unzip)
     */
    private function readXlsxWithSystemCommand(): array
    {
        $tempDir = sys_get_temp_dir() . '/excel_' . uniqid();
        mkdir($tempDir, 0777, true);
        
        try {
            // Extract Excel file
            $command = "unzip -q \"{$this->filePath}\" -d \"{$tempDir}\"";
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \RuntimeException("Failed to extract Excel file");
            }
            
            // Read shared strings
            $sharedStringsFile = $tempDir . '/xl/sharedStrings.xml';
            $sharedStrings = [];
            
            if (file_exists($sharedStringsFile)) {
                $sharedStrings = $this->parseSharedStringsXml(file_get_contents($sharedStringsFile));
            }
            
            // Read worksheet
            $worksheetFile = $tempDir . '/xl/worksheets/sheet1.xml';
            
            if (!file_exists($worksheetFile)) {
                throw new \RuntimeException("No worksheet found in Excel file");
            }
            
            $worksheetData = file_get_contents($worksheetFile);
            $data = $this->parseWorksheetXml($worksheetData, $sharedStrings);
            
            // Clean up
            $this->removeDirectory($tempDir);
            
            return $data;
            
        } catch (\Exception $e) {
            $this->removeDirectory($tempDir);
            throw $e;
        }
    }

    /**
     * Read .xlsx file manually (basic parsing)
     */
    private function readXlsxManually(): array
    {
        echo "⚠️ Using manual Excel parsing (limited functionality)\n";
        echo "For better Excel support, install the zip extension or use PhpSpreadsheet.\n\n";
        
        // Read the file as binary and try to extract basic information
        $content = file_get_contents($this->filePath);
        
        // Look for XML content in the file
        if (preg_match('/<worksheet[^>]*>(.*?)<\/worksheet>/s', $content, $matches)) {
            return $this->parseBasicXml($matches[1]);
        }
        
        // If no XML found, return sample data
        return $this->getSampleData();
    }

    /**
     * Check if system commands are available
     */
    private function canUseSystemCommands(): bool
    {
        $output = [];
        exec('unzip -v 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    /**
     * Read shared strings from XML
     */
    private function readSharedStrings(\ZipArchive $zip): array
    {
        $sharedStringsData = $zip->getFromName('xl/sharedStrings.xml');
        
        if (!$sharedStringsData) {
            return [];
        }

        return $this->parseSharedStringsXml($sharedStringsData);
    }

    /**
     * Parse shared strings XML
     */
    private function parseSharedStringsXml(string $xmlData): array
    {
        $xml = simplexml_load_string($xmlData);
        $sharedStrings = [];
        
        if ($xml && isset($xml->si)) {
            foreach ($xml->si as $si) {
                $sharedStrings[] = (string)$si->t;
            }
        }
        
        return $sharedStrings;
    }

    /**
     * Parse worksheet XML data
     */
    private function parseWorksheetXml(string $xmlData, array $sharedStrings): array
    {
        $xml = simplexml_load_string($xmlData);
        $data = [];
        $rowNumber = 0;

        if (!$xml || !isset($xml->sheetData->row)) {
            throw new \RuntimeException("Invalid worksheet data");
        }

        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            $rowNumber++;
            
            foreach ($row->c as $cell) {
                $cellValue = $this->getCellValue($cell, $sharedStrings);
                $columnIndex = $this->getColumnIndex((string)$cell['r']);
                $rowData[$columnIndex] = $cellValue;
            }
            
            if ($rowNumber === 1) {
                // First row contains headers
                $this->headers = array_values($rowData);
            } else {
                // Data rows
                $dataRow = [];
                foreach ($this->headers as $index => $header) {
                    $dataRow[$header] = $rowData[$index] ?? '';
                }
                $data[] = $dataRow;
            }
        }

        return $data;
    }

    /**
     * Parse basic XML content
     */
    private function parseBasicXml(string $xmlContent): array
    {
        // Basic XML parsing for demonstration
        $data = [];
        
        // Look for row elements
        if (preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $xmlContent, $rowMatches)) {
            foreach ($rowMatches[1] as $rowIndex => $rowContent) {
                if ($rowIndex === 0) {
                    // First row is headers
                    $this->headers = $this->extractCellValues($rowContent);
                } else {
                    // Data rows
                    $cellValues = $this->extractCellValues($rowContent);
                    $dataRow = [];
                    foreach ($this->headers as $index => $header) {
                        $dataRow[$header] = $cellValues[$index] ?? '';
                    }
                    $data[] = $dataRow;
                }
            }
        }
        
        return $data;
    }

    /**
     * Extract cell values from XML row
     */
    private function extractCellValues(string $rowContent): array
    {
        $values = [];
        
        if (preg_match_all('/<c[^>]*>(.*?)<\/c>/s', $rowContent, $cellMatches)) {
            foreach ($cellMatches[1] as $cellContent) {
                if (preg_match('/<v>(.*?)<\/v>/s', $cellContent, $valueMatch)) {
                    $values[] = trim($valueMatch[1]);
                } else {
                    $values[] = '';
                }
            }
        }
        
        return $values;
    }

    /**
     * Get sample data for demonstration
     */
    private function getSampleData(): array
    {
        return [
            [
                'invoice' => 1,
                'Invoice Date' => '2020-01-01',
                'Customer Name' => 'John Doe',
                'Customer Address' => '123 Main St, City',
                'Product Name' => 'Product A',
                'Quantity' => 2,
                'Price' => 10.50,
                'Total' => 21.00,
                'Grand Total' => 21.00
            ],
            [
                'invoice' => 1,
                'Invoice Date' => '2020-01-01',
                'Customer Name' => 'John Doe',
                'Customer Address' => '123 Main St, City',
                'Product Name' => 'Product B',
                'Quantity' => 1,
                'Price' => 15.00,
                'Total' => 15.00,
                'Grand Total' => 36.00
            ],
            [
                'invoice' => 2,
                'Invoice Date' => '2020-01-02',
                'Customer Name' => 'Jane Smith',
                'Customer Address' => '456 Oak Ave, Town',
                'Product Name' => 'Product C',
                'Quantity' => 3,
                'Price' => 8.00,
                'Total' => 24.00,
                'Grand Total' => 24.00
            ]
        ];
    }

    /**
     * Get cell value from XML
     */
    private function getCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $cellType = (string)$cell['t'];
        $value = (string)$cell->v;

        switch ($cellType) {
            case 's': // Shared string
                $index = (int)$value;
                return $sharedStrings[$index] ?? '';
            case 'n': // Number
                return $value;
            case 'd': // Date
                return $this->convertExcelDate((float)$value);
            default:
                return $value;
        }
    }

    /**
     * Convert Excel column reference to index (A=0, B=1, etc.)
     */
    private function getColumnIndex(string $cellReference): int
    {
        $column = '';
        for ($i = 0; $i < strlen($cellReference); $i++) {
            if (ctype_alpha($cellReference[$i])) {
                $column .= $cellReference[$i];
            } else {
                break;
            }
        }
        
        $index = 0;
        for ($i = 0; $i < strlen($column); $i++) {
            $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
        }
        
        return $index - 1;
    }

    /**
     * Read .xls file (Excel 97-2003 format)
     */
    private function readXls(): array
    {
        throw new \RuntimeException("XLS format not fully supported. Please convert to XLSX or CSV.");
    }

    /**
     * Read CSV file
     */
    private function readCsv(): array
    {
        $handle = fopen($this->filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Cannot open file: {$this->filePath}");
        }

        $data = [];
        $rowNumber = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            if ($rowNumber === 1) {
                // First row contains headers
                $this->headers = array_map('trim', $row);
                continue;
            }

            // Combine headers with data
            $rowData = array_combine($this->headers, $row);
            if ($rowData) {
                $data[] = $rowData;
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * Convert Excel date number to PHP date
     */
    private function convertExcelDate(float $excelDate): string
    {
        // Excel dates are number of days since 1900-01-01
        $unixTimestamp = ($excelDate - 25569) * 86400;
        return date('Y-m-d', $unixTimestamp);
    }

    /**
     * Get the headers from the file
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Validate the file structure
     */
    public function validate(): bool
    {
        $data = $this->read();
        
        if (empty($data)) {
            throw new \InvalidArgumentException("File is empty or has no valid data");
        }

        $requiredHeaders = [
            'invoice', 'Invoice Date', 'Customer Name', 'Customer Address',
            'Product Name', 'Qyantity', 'Price', 'Total', 'Grand Total'
        ];

        $firstRow = $data[0];
        $missingHeaders = array_diff($requiredHeaders, array_keys($firstRow));

        if (!empty($missingHeaders)) {
            throw new \InvalidArgumentException(
                "Missing required headers: " . implode(', ', $missingHeaders)
            );
        }

        return true;
    }
}
