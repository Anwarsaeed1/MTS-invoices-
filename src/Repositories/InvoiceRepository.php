<?php
namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Database\Connection;

class InvoiceRepository
{
    public function __construct(private Connection $connection) {}

    /**
     * Paginates the list of invoices.
     *
     * @param int $page The current page number.
     * @param int $perPage The number of items per page.
     * @return array The paginated invoice data, including the invoices and pagination metadata.
     */

    public function paginate(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        
        $invoices = $this->connection->execute("
            SELECT i.*, c.name as customer_name 
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            LIMIT ? OFFSET ?
        ", [$perPage, $offset])->fetchAll();

        $total = $this->connection->execute(
            "SELECT COUNT(*) FROM invoices"
        )->fetchColumn();

        $invoices = $this->removeNumericKeys($invoices);

        return [
            'data' => $invoices,
            'meta' => $this->buildPaginationMeta($total, $page, $perPage)
        ];
    }

    /**
     * Finds an invoice by its ID.
     *
     * @param int $id The ID of the invoice to find.
     * @return array|null The invoice data as an associative array, or null if not found.
     */
    public function findById(int $id): ?array
    {
         
        $invoice = $this->connection->execute("
            SELECT * FROM invoices WHERE id = ?
        ", [$id])->fetch();
        
        if (!$invoice) {
            return null;
        }
        
        // Remove numeric keys from single row
        return array_filter($invoice, function($key) {
            return is_string($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    
    /**
     * Retrieves the items of an invoice.
     *
     * @param int $invoiceId The ID of the invoice for which the items are to be retrieved.
     * @return array An array of invoice items with their respective product names.
     */
    public function getItems(int $invoiceId): array
    {
        $items = $this->connection->execute("
            SELECT ii.*, p.name as product_name 
            FROM invoice_items ii
            JOIN products p ON ii.product_id = p.id
            WHERE ii.invoice_id = ?
        ", [$invoiceId])->fetchAll();
        
        return $this->removeNumericKeys($items);
    }

    /**
     * Creates a new invoice in the database.
     *
     * @param array $data An associative array containing the invoice data:
     *                    - 'date': The invoice date.
     *                    - 'customer_id': The ID of the customer.
     *                    - 'grand_total': The grand total amount of the invoice.
     * @return int The ID of the newly created invoice.
     */
    public function create(array $data): int
    {
        $this->connection->execute("
            INSERT INTO invoices (invoice_date, customer_id, grand_total)
            VALUES (?, ?, ?)
        ", [$data['date'], $data['customer_id'], $data['grand_total']]);

        return $this->connection->getPdo()->lastInsertId();
    }

    /**
     * Adds an item to an invoice.
     *
     * @param int $invoiceId The ID of the invoice to which the item is to be added.
     * @param array $itemData An associative array containing item details with keys 
     *                        'product_id', 'quantity', and 'total'.
     * @return void
     */
    public function addItem(int $invoiceId, array $itemData): void
    {
        $this->connection->execute("
            INSERT INTO invoice_items (invoice_id, product_id, quantity, total)
            VALUES (?, ?, ?, ?)
        ", [$invoiceId, $itemData['product_id'], $itemData['quantity'], $itemData['total']]);
    }

    /**
     * Saves an Invoice object to the database.
     *
     * @param \AnwarSaeed\InvoiceProcessor\Models\Invoice $invoice The invoice to save.
     * @return int The ID of the saved invoice.
     */
    public function save(\AnwarSaeed\InvoiceProcessor\Models\Invoice $invoice): int
    {
        $this->connection->execute("
            INSERT INTO invoices (invoice_date, customer_id, grand_total)
            VALUES (?, ?, ?)
        ", [
            $invoice->getDate()->format('Y-m-d'),
            $invoice->getCustomer()->getId(),
            $invoice->getGrandTotal()
        ]);

        $invoiceId = $this->connection->getPdo()->lastInsertId();
        
        // Save invoice items
        foreach ($invoice->getItems() as $item) {
            $this->addItem($invoiceId, [
                'product_id' => $item->getProduct()->getId(),
                'quantity' => $item->getQuantity(),
                'total' => $item->getTotal()
            ]);
        }

        return $invoiceId;
    }

    /**
     * Builds a pagination meta array.
     *
     * @param int $total Total number of items.
     * @param int $page Current page number.
     * @param int $perPage Items per page.
     * @return array Pagination meta array with keys ['total', 'page', 'per_page', 'last_page']
     */
    private function buildPaginationMeta(int $total, int $page, int $perPage): array
    {
        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * Removes numeric keys from an array of data.
     *
     * @param array $data The data array to clean.
     * @return array The cleaned data array with only string keys.
     */
    private function removeNumericKeys(array $data): array
    {
        return array_map(function($row) {
            return array_filter($row, function($key) {
                return is_string($key);
            }, ARRAY_FILTER_USE_KEY);
        }, $data);
    }
}