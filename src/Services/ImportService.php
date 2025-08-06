<?php
namespace AnwarSaeed\InvoiceProcessor\Services;

use AnwarSaeed\InvoiceProcessor\Repositories\{
    CustomerRepository,
    ProductRepository,
    InvoiceRepository
};
use AnwarSaeed\InvoiceProcessor\Models\{
    Customer,
    Product,
    Invoice,
    InvoiceItem
};
use DateTime;

class ImportService
{
    private CustomerRepository $customerRepo;
    private ProductRepository $productRepo;
    private InvoiceRepository $invoiceRepo;

    public function __construct(
        CustomerRepository $customerRepo,
        ProductRepository $productRepo,
        InvoiceRepository $invoiceRepo
    ) {
        $this->customerRepo = $customerRepo;
        $this->productRepo = $productRepo;
        $this->invoiceRepo = $invoiceRepo;
    }

    public function importFromArray(array $data): array
    {
        $invoices = [];
        $currentInvoiceId = null;
        
        foreach ($data as $row) {
            if ($row['invoice'] !== $currentInvoiceId) {
                // Create new invoice
                $customer = $this->customerRepo->findOrCreate(
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
                $currentInvoiceId = $row['invoice'];
            }
            
            $product = $this->productRepo->findOrCreate(
                $row['Product Name'],
                $row['Price']
            );
            
            $invoice->addItem(new InvoiceItem(
                null,
                $invoice,
                $product,
                $row['Qyantity'],
                $row['Total']
            ));
        }
        
        // Save all invoices
        foreach ($invoices as $invoice) {
            $this->invoiceRepo->save($invoice);
        }
        
        return $invoices;
    }
    
    private function convertExcelDate(int $excelDate): DateTime
    {
        return (new DateTime('1899-12-30'))->add(new \DateInterval("P{$excelDate}D"));
    }
}