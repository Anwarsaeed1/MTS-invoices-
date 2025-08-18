<?php

namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\InvoiceRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Contracts\Database\DatabaseAdapterInterface;
use AnwarSaeed\InvoiceProcessor\Models\Invoice;
use AnwarSaeed\InvoiceProcessor\Models\Customer;

/**
 * Flexible Invoice Repository
 * 
 * This repository is TRULY database-agnostic!
 * It works with ANY database adapter (SQLite, MySQL, MongoDB, Redis, etc.)
 * No database-specific names or SQL queries!
 */
class FlexibleInvoiceRepository implements InvoiceRepositoryInterface
{
    private DatabaseAdapterInterface $adapter;
    private string $tableName;

    public function __construct(DatabaseAdapterInterface $adapter, string $tableName = 'invoices')
    {
        $this->adapter = $adapter;
        $this->tableName = $tableName;
    }

    public function findById(int $id): ?Invoice
    {
        $data = $this->adapter->findById($this->tableName, $id);
        
        if (!$data) {
            return null;
        }
        
        return $this->createEntityFromData($data);
    }

    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $data = $this->adapter->findAll($this->tableName, $page, $perPage);
        
        $invoices = [];
        foreach ($data as $row) {
            $invoices[] = $this->createEntityFromData($row);
        }
        
        return $invoices;
    }

    public function save(object $entity): object
    {
        if (!($entity instanceof Invoice)) {
            throw new \InvalidArgumentException('Entity must be an Invoice instance');
        }
        
        $data = [
            'customer_id' => $entity->getCustomer()->getId(),
            'invoice_date' => $entity->getDate()->format('Y-m-d'),
            'grand_total' => $entity->getGrandTotal()
        ];
        
        if ($entity->getId()) {
            // Update existing invoice
            $this->adapter->update($this->tableName, $entity->getId(), $data);
            return $entity;
        } else {
            // Create new invoice
            $id = $this->adapter->insert($this->tableName, $data);
            $entity->setId($id);
            return $entity;
        }
    }

    public function delete(int $id): bool
    {
        return $this->adapter->delete($this->tableName, $id);
    }

    public function getItems(int $invoiceId): array
    {
        // Query invoice_items table to get items for this invoice
        $items = $this->adapter->findAll('invoice_items', 1, 1000); // Get all items
        
        $invoiceItems = [];
        foreach ($items as $item) {
            if ($item['invoice_id'] == $invoiceId) {
                // Get product details for this item
                $product = $this->adapter->findById('products', $item['product_id']);
                
                $invoiceItems[] = [
                    'id' => $item['id'],
                    'product_id' => $item['product_id'],
                    'product_name' => $product ? $product['name'] : 'Unknown Product',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? $item['price'] ?? (isset($item['total'], $item['quantity']) && $item['quantity'] > 0 ? $item['total'] / $item['quantity'] : 0),
                    'total' => $item['total']
                ];
            }
        }
        
        return $invoiceItems;
    }

    public function addItem(int $invoiceId, array $itemData): bool
    {
        // Insert into invoice_items table
        $itemDataToInsert = [
            'invoice_id' => $invoiceId,
            'product_id' => $itemData['product_id'],
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['price'] ?? $itemData['unit_price'] ?? 0,
            'total' => $itemData['total']
        ];
        
        $this->adapter->insert('invoice_items', $itemDataToInsert);
        
        // Update the invoice total
        $invoice = $this->findById($invoiceId);
        if (!$invoice) {
            return false;
        }
        
        $currentTotal = $invoice->getGrandTotal();
        $newTotal = $currentTotal + ($itemData['quantity'] * ($itemData['price'] ?? $itemData['unit_price'] ?? 0));
        
        $data = [
            'grand_total' => $newTotal
        ];
        
        return $this->adapter->update($this->tableName, $invoiceId, $data);
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        return $this->findAll($page, $perPage);
    }

    public function create(array $data): int
    {
        $invoiceData = [
            'customer_id' => $data['customer_id'],
            'invoice_date' => $data['date'],
            'grand_total' => $data['grand_total'] ?? 0.0
        ];
        
        return $this->adapter->insert($this->tableName, $invoiceData);
    }

    protected function createEntityFromData(array $data): object
    {
    
        $customer = new Customer($data['customer_id'], 'Unknown Customer', 'Unknown Address');
        
        return new Invoice(
            $data['id'],
            new \DateTime($data['invoice_date']),
            $customer,
            $data['grand_total'] ?? 0.0
        );
    }
}
