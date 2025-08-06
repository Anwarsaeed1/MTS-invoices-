<?php
namespace AnwarSaeed\InvoiceProcessor\Export;

class JsonRenderer
{
    /**
     * Render a single invoice to JSON
     */
    public function renderInvoice(array $invoiceData, ?array $customerData, array $itemsData): string
    {
        $data = [
            'invoice' => $invoiceData,
            'customer' => $customerData,
            'items' => $itemsData
        ];
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Render multiple invoices to JSON
     */
    public function renderInvoices(array $invoicesData): string
    {
        return json_encode($invoicesData, JSON_PRETTY_PRINT);
    }

    /**
     * Render invoice details for API response
     */
    public function renderInvoiceDetails(array $invoiceData, ?array $customerData, array $itemsData): array
    {
        return [
            'invoice' => $invoiceData,
            'customer' => $customerData,
            'items' => $itemsData
        ];
    }

    /**
     * Render paginated invoices for API response
     */
    public function renderPaginatedInvoices(array $invoicesData, array $metaData): array
    {
        return [
            'data' => $invoicesData,
            'meta' => $metaData
        ];
    }
}