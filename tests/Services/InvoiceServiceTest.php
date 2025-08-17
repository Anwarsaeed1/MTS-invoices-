<?php

namespace AnwarSaeed\InvoiceProcessor\Tests\Services;

use PHPUnit\Framework\TestCase;
use AnwarSaeed\InvoiceProcessor\Services\InvoiceService;
use AnwarSaeed\InvoiceProcessor\Services\ExportService;
use AnwarSaeed\InvoiceProcessor\Services\ImportService;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\{
    InvoiceRepositoryInterface,
    CustomerRepositoryInterface,
    ProductRepositoryInterface
};
use AnwarSaeed\InvoiceProcessor\Exceptions\InvoiceNotFoundException;
use AnwarSaeed\InvoiceProcessor\Models\Invoice;
use AnwarSaeed\InvoiceProcessor\Models\Customer;

class InvoiceServiceTest extends TestCase
{
    private InvoiceService $invoiceService;
    private $mockInvoiceRepo;
    private $mockCustomerRepo;
    private $mockProductRepo;
    private $mockExportService;
    private $mockImportService;

    protected function setUp(): void
    {
        // Create mock repositories
        $this->mockInvoiceRepo = $this->createMock(InvoiceRepositoryInterface::class);
        $this->mockCustomerRepo = $this->createMock(CustomerRepositoryInterface::class);
        $this->mockProductRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->mockExportService = $this->createMock(ExportService::class);
        $this->mockImportService = $this->createMock(ImportService::class);

        // Create service with mock dependencies
        $this->invoiceService = new InvoiceService(
            $this->mockInvoiceRepo,
            $this->mockCustomerRepo,
            $this->mockProductRepo,
            $this->mockExportService,
            $this->mockImportService
        );
    }

    public function testGetPaginatedInvoices(): void
    {
        // Arrange
        $expectedData = [
            'data' => [
                [
                    'id' => 1,
                    'invoice_date' => '2024-01-15',
                    'customer_id' => 1,
                    'grand_total' => 100.00,
                    'customer_name' => 'John Doe'
                ]
            ],
            'meta' => [
                'total' => 1,
                'page' => 1,
                'per_page' => 20,
                'last_page' => 1
            ]
        ];

        $this->mockInvoiceRepo
            ->expects($this->once())
            ->method('paginate')
            ->with(1, 20)
            ->willReturn($expectedData);

        // Act
        $result = $this->invoiceService->getPaginatedInvoices(1, 20);

        // Assert
        $this->assertEquals($expectedData, $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(1, $result['data']);
    }

    public function testGetInvoiceDetailsThrowsExceptionWhenInvoiceNotFound(): void
    {
        // Arrange
        $invoiceId = 999;

        $this->mockInvoiceRepo
            ->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn(null);

        // Act & Assert
        $this->expectException(InvoiceNotFoundException::class);
        $this->expectExceptionMessage('Invoice not found');

        $this->invoiceService->getInvoiceDetails($invoiceId);
    }

    public function testGetInvoiceDetailsReturnsCorrectData(): void
    {
        // Arrange
        $invoiceId = 1;
        $customer = new Customer(1, 'John Doe', '123 Main St');
        $invoice = new Invoice(1, new \DateTime('2024-01-15'), $customer, 100.00);
        
        $itemsData = [
            [
                'id' => 1,
                'product_name' => 'Product A',
                'quantity' => 2,
                'total' => 50.00
            ]
        ];

        $this->mockInvoiceRepo
            ->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->mockInvoiceRepo
            ->expects($this->once())
            ->method('getItems')
            ->with($invoiceId)
            ->willReturn($itemsData);

        $this->mockCustomerRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($customer);

        // Act
        $result = $this->invoiceService->getInvoiceDetails($invoiceId);

        // Assert
        $this->assertArrayHasKey('invoice', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('customer', $result);
        $this->assertEquals(1, $result['invoice']['id']);
        $this->assertEquals('2024-01-15', $result['invoice']['invoice_date']);
        $this->assertEquals(100.00, $result['invoice']['grand_total']);
        $this->assertEquals($itemsData, $result['items']);
    }
} 