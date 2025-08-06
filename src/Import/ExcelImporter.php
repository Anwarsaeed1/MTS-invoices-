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
        // Mock implementation - replace with real Excel reader
        return [
            [
                'invoice' => 1,
                'Invoice Date' => 43831,
                'Customer Name' => 'Idaline Mateuszczyk',
                'Customer Address' => '95798 Fieldstone Point',
                'Product Name' => 'Bread - Granary Small Pull',
                'Qyantity' => 1,
                'Price' => 10.5,
                'Total' => 10.5,
                'Grand Total' => 35.5
            ],
            
            // Add more mock data as needed
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