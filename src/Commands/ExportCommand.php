<?php

namespace AnwarSaeed\InvoiceProcessor\Commands;

use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Repositories\InvoiceRepository;
use AnwarSaeed\InvoiceProcessor\Repositories\CustomerRepository;
use AnwarSaeed\InvoiceProcessor\Repositories\ProductRepository;

class ExportCommand
{
    private InvoiceRepository $invoiceRepo;
    private CustomerRepository $customerRepo;
    private ProductRepository $productRepo;

    public function __construct()
    {
        $connection = new Connection("sqlite:" . __DIR__ . "/../../database/invoices.db");
        $this->invoiceRepo = new InvoiceRepository($connection);
        $this->customerRepo = new CustomerRepository($connection);
        $this->productRepo = new ProductRepository($connection);
    }

    public function execute(array $args): void
    {
        $format = $args[0] ?? 'json';
        
        if (!in_array($format, ['json', 'xml'])) {
            echo "Error: Invalid format. Use 'json' or 'xml'.\n";
            exit(1);
        }

        try {
            $data = $this->getAllData();
            
            switch ($format) {
                case 'json':
                    $this->exportToJson($data);
                    break;
                case 'xml':
                    $this->exportToXml($data);
                    break;
            }
            
        } catch (\Exception $e) {
            echo "Error during export: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function getAllData(): array
    {
        $invoices = $this->invoiceRepo->paginate(1, 1000)['data'];
        $result = [];

        foreach ($invoices as $invoice) {
            $items = $this->invoiceRepo->getItems($invoice['id']);
            $customer = $this->customerRepo->findById($invoice['customer_id']);
            
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

    private function exportToJson(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    private function exportToXml(array $data): void
    {
        header('Content-Type: application/xml');
        
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><invoices></invoices>');
        
        foreach ($data as $invoiceData) {
            $invoice = $xml->addChild('invoice');
            $invoice->addChild('id', $invoiceData['invoice']['id']);
            $invoice->addChild('date', $invoiceData['invoice']['invoice_date']);
            $invoice->addChild('grand_total', $invoiceData['invoice']['grand_total']);
            
            if ($invoiceData['customer']) {
                $customer = $invoice->addChild('customer');
                $customer->addChild('id', $invoiceData['customer']['id']);
                $customer->addChild('name', $invoiceData['customer']['name']);
                $customer->addChild('address', $invoiceData['customer']['address']);
            }
            
            $items = $invoice->addChild('items');
            foreach ($invoiceData['items'] as $item) {
                $itemNode = $items->addChild('item');
                $itemNode->addChild('product_name', $item['product_name']);
                $itemNode->addChild('quantity', $item['quantity']);
                $itemNode->addChild('total', $item['total']);
            }
        }
        
        echo $xml->asXML();
    }
} 