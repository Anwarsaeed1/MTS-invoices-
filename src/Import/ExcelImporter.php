<?php
namespace AnwarSaeed\InvoiceProcessor\Import;

use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Models\Customer;
use AnwarSaeed\InvoiceProcessor\Models\Invoice;
use AnwarSaeed\InvoiceProcessor\Models\InvoiceItem;
use AnwarSaeed\InvoiceProcessor\Models\Product;
use AnwarSaeed\InvoiceProcessor\Repositories\CustomerRepository;
use AnwarSaeed\InvoiceProcessor\Repositories\ProductRepository;
use AnwarSaeed\InvoiceProcessor\Repositories\InvoiceRepository;

class ExcelImporter
{
    private Connection $connection;
    private CustomerRepository $customerRepository;
    private ProductRepository $productRepository;
    private InvoiceRepository $invoiceRepository;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->customerRepository = new CustomerRepository($connection);
        $this->productRepository = new ProductRepository($connection);
        $this->invoiceRepository = new InvoiceRepository($connection);
    }

    public function import(string $filePath): void
    {
        $data = $this->readExcel($filePath);
        $invoices = $this->processData($data);
        
        foreach ($invoices as $invoice) {
            $this->invoiceRepository->save($invoice);
        }
    }

    private function readExcel(string $filePath): array
    {
        // Mock implementation , i will use PhpSpreadsheet or other Excel reader if need data from this 
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
            ],
            [
                'invoice' => 2,
                'Invoice Date' => 43832,
                'Customer Name' => 'Jane Smith',
                'Customer Address' => '456 Oak Ave, Town',
                'Product Name' => 'Product C',
                'Qyantity' => 3,
                'Price' => 8.00,
                'Total' => 24.00,
                'Grand Total' => 24.00
            ],
            [
                'invoice' => 3,
                'Invoice Date' => 43833,
                'Customer Name' => 'Bob Johnson',
                'Customer Address' => '789 Pine Rd, Village',
                'Product Name' => 'Product D',
                'Qyantity' => 1,
                'Price' => 25.00,
                'Total' => 25.00,
                'Grand Total' => 25.00
            ]
    

            
        ];
    }

    private function processData(array $data): array
    {
        $invoices = [];
        $currentInvoiceId = null;
        
        foreach ($data as $row) {
            if ($row['invoice'] !== $currentInvoiceId) {
                // New invoice
                $currentInvoiceId = $row['invoice'];
                
                $customer = $this->customerRepository->findOrCreate(
                    $row['Customer Name'],
                    $row['Customer Address']
                );
                
                $invoice = new Invoice(
                    null,
                    $this->convertExcelDate($row['Invoice Date']),
                    $customer,
                    $row['Grand Total']
                );
                
                $invoices[] = $invoice;
            }
            
            $product = $this->productRepository->findOrCreate(
                $row['Product Name'],
                $row['Price']
            );
            
            $invoiceItem = new InvoiceItem(
                null,
                $invoice,
                $product,
                $row['Qyantity'],
                $row['Total']
            );
            
            $invoice->addItem($invoiceItem);
        }
        
        return $invoices;
    }

    private function convertExcelDate(int $excelDate): \DateTime
    {
        return \DateTime::createFromFormat('Y-m-d', '1899-12-30')
            ->add(new \DateInterval("P{$excelDate}D"));
    }
}