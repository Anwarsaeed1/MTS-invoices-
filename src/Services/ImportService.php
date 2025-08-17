<?php

namespace AnwarSaeed\InvoiceProcessor\Services;

use AnwarSaeed\InvoiceProcessor\Contracts\Import\ImportStrategyInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\InvoiceRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\CustomerRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\ProductRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Import\ExcelImportStrategy;
use AnwarSaeed\InvoiceProcessor\Exceptions\ImportException;

class ImportService
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private CustomerRepositoryInterface $customerRepository;
    private ProductRepositoryInterface $productRepository;
    private array $strategies = [];

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        
        // Register default strategies
        $this->registerStrategy(new ExcelImportStrategy());
    }

    public function registerStrategy(ImportStrategyInterface $strategy): void
    {
        $this->strategies[] = $strategy;
    }

    public function import(string $filePath): array
    {
        $strategy = $this->findStrategy($filePath);
        
        if (!$strategy) {
            throw new ImportException("No import strategy found for file: {$filePath}");
        }

        try {
            $data = $strategy->import($filePath);
            return $this->processData($data);
        } catch (\Exception $e) {
            throw new ImportException("Import failed: " . $e->getMessage());
        }
    }

    private function findStrategy(string $filePath): ?ImportStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->canHandle($filePath)) {
                return $strategy;
            }
        }
        
        return null;
    }

    private function processData(array $data): array
    {
        $results = [
            'invoices' => 0,
            'customers' => 0,
            'products' => 0,
            'items' => 0
        ];

        $currentInvoiceId = null;
        $currentInvoice = null;

        foreach ($data as $row) {
            if ($row['invoice'] !== $currentInvoiceId) {
                // New invoice
                $currentInvoiceId = $row['invoice'];
                
                $customer = $this->customerRepository->findOrCreate(
                    $row['Customer Name'],
                    $row['Customer Address']
                );
                
                $invoiceId = $this->invoiceRepository->create([
                    'date' => $this->convertExcelDate($row['Invoice Date']),
                    'customer_id' => $customer->getId(),
                    'grand_total' => $row['Grand Total']
                ]);
                
                $currentInvoice = $invoiceId;
                $results['invoices']++;
                $results['customers']++;
            }
            
            $product = $this->productRepository->findOrCreate(
                $row['Product Name'],
                $row['Price']
            );
            
            $this->invoiceRepository->addItem($currentInvoice, [
                'product_id' => $product->getId(),
                'quantity' => $row['Quantity'],
                'total' => $row['Total']
            ]);
            
            $results['products']++;
            $results['items']++;
        }

        return $results;
    }

    private function convertExcelDate($excelDate): string
    {
        if (is_numeric($excelDate)) {
            // Excel dates are number of days since 1900-01-01
            $date = \DateTime::createFromFormat('Y-m-d', '1899-12-30')
                ->add(new \DateInterval("P{$excelDate}D"));
            return $date->format('Y-m-d');
        }
        
        // If it's already a string date, return as is
        return $excelDate;
    }
}