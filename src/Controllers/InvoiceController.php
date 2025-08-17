<?php
namespace AnwarSaeed\InvoiceProcessor\Controllers;

use AnwarSaeed\InvoiceProcessor\Contracts\Services\InvoiceServiceInterface;
use AnwarSaeed\InvoiceProcessor\Exceptions\{
    InvoiceNotFoundException, 
    ImportException
};

class InvoiceController
{
    public function __construct(private InvoiceServiceInterface $invoiceService) {}

    public function list(int $page = 1, int $perPage = 20): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->invoiceService->getPaginatedInvoices($page, $perPage));
    }

    public function show(int $id): void
    {
        header('Content-Type: application/json');
        try {
            echo json_encode($this->invoiceService->getInvoiceDetails($id));
        } catch (InvoiceNotFoundException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function import(string $filePath): void
    {
        header('Content-Type: application/json');
        try {
            echo json_encode([
                'success' => true,
                'data' => $this->invoiceService->importFromFile($filePath)
            ]);
        } catch (ImportException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}