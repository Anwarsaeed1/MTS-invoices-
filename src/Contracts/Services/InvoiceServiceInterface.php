<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Services;

interface InvoiceServiceInterface
{
    /**
     * Get paginated invoices
     */
    public function getPaginatedInvoices(int $page = 1, int $perPage = 20): array;
    
    /**
     * Get invoice details by ID
     */
    public function getInvoiceDetails(int $id): array;
    
    /**
     * Import invoices from file
     */
    public function importFromFile(string $filePath): array;
    
    /**
     * Export invoices to specified format
     */
    public function exportInvoices(string $format = 'json'): string;
}
