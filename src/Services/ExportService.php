<?php

namespace AnwarSaeed\InvoiceProcessor\Services;

use AnwarSaeed\InvoiceProcessor\Contracts\Export\ExportStrategyInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\InvoiceRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\CustomerRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Export\JsonExportStrategy;
use AnwarSaeed\InvoiceProcessor\Export\XmlExportStrategy;

class ExportService
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private CustomerRepositoryInterface $customerRepository;
    private array $strategies = [];

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->customerRepository = $customerRepository;
        
        // Register default strategies
        $this->registerStrategy('json', new JsonExportStrategy());
        $this->registerStrategy('xml', new XmlExportStrategy());
    }

    public function registerStrategy(string $format, ExportStrategyInterface $strategy): void
    {
        $this->strategies[$format] = $strategy;
    }

    public function export(string $format = 'json'): string
    {
        if (!isset($this->strategies[$format])) {
            throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }

        $data = $this->prepareData();
        return $this->strategies[$format]->export($data);
    }

    public function getContentType(string $format = 'json'): string
    {
        if (!isset($this->strategies[$format])) {
            throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }

        return $this->strategies[$format]->getContentType();
    }

    private function prepareData(): array
    {
        $invoices = $this->invoiceRepository->paginate(1, 1000)['data'];
        $result = [];

        foreach ($invoices as $invoice) {
            $items = $this->invoiceRepository->getItems($invoice['id']);
            $customer = $this->customerRepository->findById($invoice['customer_id']);
            
            $result[] = [
                'invoice' => $invoice,
                'customer' => $customer ? [
                    'id' => $customer->getId(),
                    'name' => $customer->getName(),
                    'address' => $customer->getAddress()
                ] : null,
                'items' => $items
            ];
        }

        return $result;
    }
}
