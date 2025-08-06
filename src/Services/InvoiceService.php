<?php
namespace AnwarSaeed\InvoiceProcessor\Services;

use AnwarSaeed\InvoiceProcessor\Repositories\{
    InvoiceRepository,
    CustomerRepository,
    ProductRepository
};
use AnwarSaeed\InvoiceProcessor\Exceptions\{
    InvoiceNotFoundException,
    ImportException
};

class InvoiceService
{
    public function __construct(
        private InvoiceRepository $invoiceRepo,
        private CustomerRepository $customerRepo,
        private ProductRepository $productRepo
    ) {}

    public function getPaginatedInvoices(int $page = 1, int $perPage = 20): array
    {
        return $this->invoiceRepo->paginate($page, $perPage);
    }

    public function getInvoiceDetails(int $id): array
    {
        $invoice = $this->invoiceRepo->findById($id);
        
        if (!$invoice) {
            throw new InvoiceNotFoundException("Invoice not found");
        }

        $customer = $this->customerRepo->findById($invoice['customer_id']);
        return [
            'invoice' => $invoice,
            'items' => $this->invoiceRepo->getItems($id),
            'customer' => $customer ? [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'address' => $customer->getAddress()
            ] : []
        ];  
    }

    public function importFromFile(string $filePath): array
    {
        try {
            $data = $this->parseExcel($filePath);
            return $this->processImport($data);
        } catch (\Exception $e) {
            throw new ImportException("Import failed: " . $e->getMessage());
        }
    }

    private function parseExcel(string $filePath): array
    {
        // Mock implementation - replace with real Excel reader
        return [
            [
                'Invoice Date' => '2024-01-15',
                'Customer Name' => 'John Doe',
                'Customer Address' => '123 Main St',
                'Product Name' => 'Product A',
                'Qyantity' => 2,
                'Price' => 10.50,
                'Total' => 21.00,
                'Grand Total' => 21.00
            ]
        ];
    }

    private function processImport(array $data): array
    {
        $results = [
            'invoices' => 0,
            'customers' => 0,
            'products' => 0,
            'items' => 0
        ];

        foreach ($data as $row) {
            $customer = $this->customerRepo->findOrCreate(
                $row['Customer Name'],
                $row['Customer Address']
            );
            
            $product = $this->productRepo->findOrCreate(
                $row['Product Name'],
                $row['Price']
            );
            
            $invoiceId = $this->invoiceRepo->create([
                'date' => $row['Invoice Date'],
                'customer_id' => $customer->getId(),
                'grand_total' => $row['Grand Total']
            ]);
            
            $this->invoiceRepo->addItem($invoiceId, [
                'product_id' => $product->getId(),
                'quantity' => $row['Qyantity'],
                'total' => $row['Total']
            ]);
            
            $results['invoices']++;
            $results['items']++;
        }

        return $results;
    }

}