<?php

namespace AnwarSaeed\InvoiceProcessor\Tests\Import;

use PHPUnit\Framework\TestCase;
use AnwarSaeed\InvoiceProcessor\Import\PhpSpreadsheetReader;
use InvalidArgumentException;
use Exception;

class PhpSpreadsheetReaderTest extends TestCase
{
    private PhpSpreadsheetReader $reader;

    protected function setUp(): void
    {
        $this->reader = new PhpSpreadsheetReader();
    }

    /**
     * Test reading a valid CSV file
     */
    public function testReadCsvFile(): void
    {
        $filePath = __DIR__ . '/../data/test_invoices.csv';
        
        if (!file_exists($filePath)) {
            $this->markTestSkipped('Test CSV file not found');
        }

        $data = $this->reader->read($filePath);
        
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey('invoice_date', $data[0]);
        $this->assertArrayHasKey('customer_name', $data[0]);
    }

    /**
     * Test file not found exception
     */
    public function testFileNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found: nonexistent_file.xlsx');
        
        $this->reader->read('nonexistent_file.xlsx');
    }

    /**
     * Test unsupported file format
     */
    public function testUnsupportedFileFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        // Create a temporary text file
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');
        
        try {
            $this->reader->read($tempFile);
        } finally {
            unlink($tempFile);
        }
    }

    /**
     * Test canHandle method
     */
    public function testCanHandle(): void
    {
        $this->assertTrue($this->reader->canHandle('test.xlsx'));
        $this->assertTrue($this->reader->canHandle('test.xls'));
        $this->assertTrue($this->reader->canHandle('test.csv'));
        $this->assertTrue($this->reader->canHandle('test.ods'));
        $this->assertFalse($this->reader->canHandle('test.txt'));
    }

    /**
     * Test getSupportedExtensions method
     */
    public function testGetSupportedExtensions(): void
    {
        $extensions = $this->reader->getSupportedExtensions();
        
        $this->assertIsArray($extensions);
        $this->assertContains('xlsx', $extensions);
        $this->assertContains('xls', $extensions);
        $this->assertContains('csv', $extensions);
        $this->assertContains('ods', $extensions);
    }

    /**
     * Test reading Excel file with data.xlsx
     */
    public function testReadExcelFile(): void
    {
        $filePath = __DIR__ . '/../../data.xlsx';
        
        if (!file_exists($filePath)) {
            $this->markTestSkipped('data.xlsx file not found');
        }

        $data = $this->reader->read($filePath);
        
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey(0, $data);
        
        // Check that required fields are present
        $firstRow = $data[0];
        $this->assertArrayHasKey('invoice_date', $firstRow);
        $this->assertArrayHasKey('customer_name', $firstRow);
        $this->assertArrayHasKey('customer_address', $firstRow);
        $this->assertArrayHasKey('product_name', $firstRow);
        // Check for quantity field (handles typo 'qyantity')
        $this->assertTrue(
            array_key_exists('quantity', $firstRow) || array_key_exists('qyantity', $firstRow),
            'Quantity field not found (expected either "quantity" or "qyantity")'
        );
        $this->assertArrayHasKey('price', $firstRow);
    }
}
