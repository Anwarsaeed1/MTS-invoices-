<?php
namespace AnwarSaeed\InvoiceProcessor\Services;

use AnwarSaeed\InvoiceProcessor\Contracts\Services\InvoiceServiceInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\{
    InvoiceRepositoryInterface,
    CustomerRepositoryInterface,
    ProductRepositoryInterface
};
use AnwarSaeed\InvoiceProcessor\Exceptions\{
    InvoiceNotFoundException,
    ImportException
};
use AnwarSaeed\InvoiceProcessor\Repositories\FlexibleCustomerRepository;
use AnwarSaeed\InvoiceProcessor\Repositories\FlexibleInvoiceRepository;
use AnwarSaeed\InvoiceProcessor\Repositories\FlexibleProductRepository;

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(
        private FlexibleInvoiceRepository $invoiceRepo,
        private FlexibleCustomerRepository $customerRepo,
        private FlexibleProductRepository $productRepo,
        private ExportService $exportService,
        private ImportService $importService
    ) {}

    public function getPaginatedInvoices(int $page = 1, int $perPage = 20): array
    {
        $invoices = $this->invoiceRepo->paginate($page, $perPage);
        
        // Convert Invoice objects to arrays for API response (WITH customer data)
        $result = [];
        foreach ($invoices as $invoice) {
            // Get customer details for each invoice
            $customer = $this->customerRepo->findById($invoice->getCustomer()->getId());
            
            $result[] = [
                'id' => $invoice->getId(),
                'date' => $invoice->getDate()->format('Y-m-d'),
                'customer' => $customer ? [
                    'id' => $customer->getId(),
                    'name' => $customer->getName(),
                    'address' => $customer->getAddress()
                ] : null,
                'grand_total' => $invoice->getGrandTotal()
            ];
        }
        
        return $result;
    }

    public function getInvoiceDetails(int $id): array
    {
        $invoice = $this->invoiceRepo->findById($id);
        
        if (!$invoice) {
            throw new InvoiceNotFoundException("Invoice not found");
        }

        $customer = $this->customerRepo->findById($invoice->getCustomer()->getId());
        return [
            'invoice' => [
                'id' => $invoice->getId(),
                'invoice_date' => $invoice->getDate()->format('Y-m-d'),
                'customer_id' => $invoice->getCustomer()->getId(),
                'grand_total' => $invoice->getGrandTotal()
            ],
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
        return $this->importService->import($filePath);
    }
    
    public function exportInvoices(string $format = 'json'): string
    {
        return $this->exportService->export($format);
    }

}