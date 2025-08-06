<?php
namespace AnwarSaeed\InvoiceProcessor\Export;

use AnwarSaeed\InvoiceProcessor\Models\Invoice;

class JsonRenderer
{
    public function render(Invoice $invoice): string
    {
        $data = [
            'invoice_id' => $invoice->getId(),
            'date' => $invoice->getDate()->format('Y-m-d'),
            'customer' => [
                'name' => $invoice->getCustomer()->getName(),
                'address' => $invoice->getCustomer()->getAddress(),
            ],
            'items' => array_map(function($item) {
                return [
                    'product' => $item->getProduct()->getName(),
                    'quantity' => $item->getQuantity(),
                    'unit_price' => $item->getProduct()->getPrice(),
                    'total' => $item->getTotal(),
                ];
            }, $invoice->getItems()),
            'grand_total' => $invoice->getGrandTotal(),
        ];
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}