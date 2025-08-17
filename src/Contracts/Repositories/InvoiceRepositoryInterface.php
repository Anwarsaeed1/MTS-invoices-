<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Repositories;

use AnwarSaeed\InvoiceProcessor\Models\Invoice;

interface InvoiceRepositoryInterface extends RepositoryInterface
{
    /**
     * Get invoice items by invoice ID
     */
    public function getItems(int $invoiceId): array;
    
    /**
     * Add an item to an invoice
     */
    public function addItem(int $invoiceId, array $itemData): bool;
    
    /**
     * Get paginated invoices
     */
    public function paginate(int $page = 1, int $perPage = 20): array;
    
    /**
     * Create a new invoice
     */
    public function create(array $data): int;
}
