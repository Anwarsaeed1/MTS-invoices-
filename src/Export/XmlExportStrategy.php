<?php

namespace AnwarSaeed\InvoiceProcessor\Export;

use AnwarSaeed\InvoiceProcessor\Contracts\Export\ExportStrategyInterface;

class XmlExportStrategy implements ExportStrategyInterface
{

    /**
     * Export the given data as XML.
     *
     * @param array $data The data to export, containing invoices and their associated customers and items.
     * @return string The exported data as XML.
     */
    public function export(array $data): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><invoices></invoices>');
        
        foreach ($data as $invoiceData) {
            $invoice = $xml->addChild('invoice');
            
            // Handle different data structures
            if (isset($invoiceData['invoice']) && is_array($invoiceData['invoice'])) {
                $invoice->addChild('id', $invoiceData['invoice']['id'] ?? '');
                $invoice->addChild('date', $invoiceData['invoice']['invoice_date'] ?? '');
                $invoice->addChild('grand_total', $invoiceData['invoice']['grand_total'] ?? '');
            } else {
                // Handle simple data structure
                $invoice->addChild('id', $invoiceData['id'] ?? '');
                $invoice->addChild('date', $invoiceData['date'] ?? '');
                $invoice->addChild('grand_total', $invoiceData['grand_total'] ?? '');
            }
            
            if (isset($invoiceData['customer']) && is_array($invoiceData['customer'])) {
                $customer = $invoice->addChild('customer');
                $customer->addChild('id', $invoiceData['customer']['id'] ?? '');
                $customer->addChild('name', $invoiceData['customer']['name'] ?? '');
                $customer->addChild('address', $invoiceData['customer']['address'] ?? '');
            }
            
            $items = $invoice->addChild('items');
            if (isset($invoiceData['items']) && is_array($invoiceData['items'])) {
                foreach ($invoiceData['items'] as $item) {
                    $itemNode = $items->addChild('item');
                    $itemNode->addChild('product_name', $item['product_name'] ?? '');
                    $itemNode->addChild('quantity', $item['quantity'] ?? '');
                    $itemNode->addChild('total', $item['total'] ?? '');
                }
            }
        }
        
        return $xml->asXML();
    }
    
    /**
     * Returns the content type of the exported data.
     *
     * @return string The content type 'application/xml'.
     */
    public function getContentType(): string
    {
        return 'application/xml';
    }
    
    /**
     * Returns the file extension for the exported data.
     *
     * @return string The file extension 'xml'.
     */
    public function getFileExtension(): string
    {
        return 'xml';
    }
}
