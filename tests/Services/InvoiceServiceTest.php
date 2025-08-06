<?php

namespace AnwarSaeed\InvoiceProcessor\Tests\Services;

use PHPUnit\Framework\TestCase;
use AnwarSaeed\InvoiceProcessor\Services\InvoiceService;
use AnwarSaeed\InvoiceProcessor\Repositories\{
    InvoiceRepository,
    CustomerRepository,
    ProductRepository
};
use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Exceptions\InvoiceNotFoundException;
use AnwarSaeed\InvoiceProcessor\Models\Customer;

class InvoiceServiceTest extends TestCase
{
    private InvoiceService $invoiceService;
    private $mockInvoiceRepo;
    private $mockCustomerRepo;
    private $mockProductRepo;

    protected function setUp(): void
    {
        // Create mock repositories
        $this->mockInvoiceRepo = $this->createMock(InvoiceRepository::class);
        $this->mockCustomerRepo = $this->createMock(CustomerRepository::class);
        $this->mockProductRepo = $this->createMock(ProductRepository::class);

        // Create service with mock dependencies
        $this->invoiceService = new InvoiceService(
            $this->mockInvoiceRepo,
            $this->mockCustomerRepo,
            $this->mockProductRepo
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
        $invoiceData = [
            'id' => 1,
            'invoice_date' => '2024-01-15',
            'customer_id' => 1,
            'grand_total' => 100.00
        ];
        $itemsData = [
            [
                'id' => 1,
                'product_name' => 'Product A',
                'quantity' => 2,
                'total' => 50.00
            ]
        ];
        $customerData = [
            'id' => 1,
            'name' => 'John Doe',
            'address' => '123 Main St'
        ];

        $this->mockInvoiceRepo
            ->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoiceData);

        $this->mockInvoiceRepo
            ->expects($this->once())
            ->method('getItems')
            ->with($invoiceId)
            ->willReturn($itemsData);

        $this->mockCustomerRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn(new Customer(
                $customerData['id'],
                $customerData['name'],
                $customerData['address']
            ));

        // Act
        $result = $this->invoiceService->getInvoiceDetails($invoiceId);

        // Assert
        $this->assertArrayHasKey('invoice', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('customer', $result);
        $this->assertEquals($invoiceData, $result['invoice']);
        $this->assertEquals($itemsData, $result['items']);
    }
} 