<?php

namespace Tests\Import;

use PHPUnit\Framework\TestCase;
use AnwarSaeed\InvoiceProcessor\Import\SimpleExcelReader;

class SimpleExcelReaderTest extends TestCase
{
    private string $testCsvFile;
    private string $testDataDir;

    protected function setUp(): void
    {
        $this->testDataDir = __DIR__ . '/../../tests/data';
        $this->testCsvFile = $this->testDataDir . '/test_invoices.csv';
        
        // Create test data directory if it doesn't exist
        if (!is_dir($this->testDataDir)) {
            mkdir($this->testDataDir, 0755, true);
        }
        
        // Create test CSV file
        $this->createTestCsvFile();
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (file_exists($this->testCsvFile)) {
            unlink($this->testCsvFile);
        }
    }

    private function createTestCsvFile(): void
    {
        $csvData = [
            ['invoice', 'Invoice Date', 'Customer Name', 'Customer Address', 'Product Name', 'Quantity', 'Price', 'Total', 'Grand Total'],
            ['1', '2020-01-01', 'John Doe', '123 Main St, City', 'Product A', '2', '10.50', '21.00', '21.00'],
            ['1', '2020-01-01', 'John Doe', '123 Main St, City', 'Product B', '1', '15.00', '15.00', '36.00'],
            ['2', '2020-01-02', 'Jane Smith', '456 Oak Ave, Town', 'Product C', '3', '8.00', '24.00', '24.00']
        ];

        $handle = fopen($this->testCsvFile, 'w');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }

    public function testReadCsvFile(): void
    {
        $reader = new SimpleExcelReader($this->testCsvFile);
        $data = $reader->read();

        $this->assertIsArray($data);
        $this->assertCount(3, $data); // 3 data rows (excluding header)

        // Check first row structure
        $firstRow = $data[0];
        $this->assertEquals(1, $firstRow['invoice']);
        $this->assertEquals('2020-01-01', $firstRow['Invoice Date']);
        $this->assertEquals('John Doe', $firstRow['Customer Name']);
        $this->assertEquals('123 Main St, City', $firstRow['Customer Address']);
        $this->assertEquals('Product A', $firstRow['Product Name']);
        $this->assertEquals(2, $firstRow['Quantity']);
        $this->assertEquals(10.50, $firstRow['Price']);
        $this->assertEquals(21.00, $firstRow['Total']);
        $this->assertEquals(21.00, $firstRow['Grand Total']);
    }

    public function testValidateFileStructure(): void
    {
        $reader = new SimpleExcelReader($this->testCsvFile);
        
        $this->assertTrue($reader->validate());
    }

    public function testValidateFileWithMissingHeaders(): void
    {
        // Create invalid CSV file
        $invalidCsvFile = $this->testDataDir . '/invalid_invoices.csv';
        $csvData = [
            ['invoice', 'Customer Name'], // Missing required headers
            ['1', 'John Doe']
        ];

        $handle = fopen($invalidCsvFile, 'w');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        $reader = new SimpleExcelReader($invalidCsvFile);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required headers');
        
        $reader->validate();

        // Clean up
        unlink($invalidCsvFile);
    }

    public function testFileNotFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found');
        
        $reader = new SimpleExcelReader('nonexistent_file.csv');
        $reader->read();
    }

    public function testConvertExcelDate(): void
    {
        // Test numeric Excel date
        $excelDate = 43831; // 2020-01-01 in Excel
        $converted = SimpleExcelReader::convertExcelDate($excelDate);
        $this->assertEquals('2020-01-01', $converted);

        // Test string date
        $stringDate = '2020-01-01';
        $converted = SimpleExcelReader::convertExcelDate($stringDate);
        $this->assertEquals('2020-01-01', $converted);
    }

    public function testGetHeaders(): void
    {
        $reader = new SimpleExcelReader($this->testCsvFile);
        $reader->read(); // Read file to populate headers
        
        $headers = $reader->getHeaders();
        
        $expectedHeaders = [
            'invoice', 'Invoice Date', 'Customer Name', 'Customer Address',
            'Product Name', 'Quantity', 'Price', 'Total', 'Grand Total'
        ];
        
        $this->assertEquals($expectedHeaders, $headers);
    }
}
