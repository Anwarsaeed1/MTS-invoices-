<?php
namespace AnwarSaeed\InvoiceProcessor\Controllers;

use AnwarSaeed\InvoiceProcessor\Services\InvoiceService;
use AnwarSaeed\InvoiceProcessor\Exceptions\{
    InvoiceNotFoundException,
    ImportException
};

class InvoiceController
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function list(int $page = 1, int $perPage = 20): array
    {
        return $this->invoiceService->getPaginatedInvoices($page, $perPage);
    }

    public function show(int $id): array
    {
        try {
            return $this->invoiceService->getInvoiceDetails($id);
        } catch (InvoiceNotFoundException $e) {
            throw new \RuntimeException($e->getMessage(), 404);
        }
    }

    public function import(string $filePath): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->invoiceService->importFromFile($filePath)
            ];
        } catch (ImportException $e) {
            throw new \RuntimeException($e->getMessage(), 400);
        }
    }
}