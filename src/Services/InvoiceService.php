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

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepo,
        private CustomerRepositoryInterface $customerRepo,
        private ProductRepositoryInterface $productRepo,
        private ExportService $exportService,
        private ImportService $importService
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