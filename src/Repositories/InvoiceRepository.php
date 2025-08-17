<?php
namespace AnwarSaeed\InvoiceProcessor\Repositories;

use AnwarSaeed\InvoiceProcessor\Contracts\Repositories\InvoiceRepositoryInterface;
use AnwarSaeed\InvoiceProcessor\Models\Invoice;

class InvoiceRepository extends AbstractRepository implements InvoiceRepositoryInterface
{
    protected string $table = 'invoices';
    protected string $entityClass = Invoice::class;

    public function __construct(\AnwarSaeed\InvoiceProcessor\Contracts\Database\ConnectionInterface $connection)
    {
        parent::__construct($connection);
    }

    /**
     * Paginates the list of invoices.
     *
     * @param int $page The current page number.
     * @param int $perPage The number of items per page.
     * @return array The paginated invoice data, including the invoices and pagination metadata.
     */

    public function paginate(int $page = 1, int $perPage = 20): array
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

    public function findById(int $id): ?object
    {
        $result = parent::findById($id);
        return $result instanceof Invoice ? $result : null;
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

    public function addItem(int $invoiceId, array $itemData): bool
    {
        $this->connection->execute("
            INSERT INTO invoice_items (invoice_id, product_id, quantity, total)
            VALUES (?, ?, ?, ?)
        ", [$invoiceId, $itemData['product_id'], $itemData['quantity'], $itemData['total']]);
        
        return true;
    }

    public function save(object $entity): object
    {
        if (!($entity instanceof Invoice)) {
            throw new \InvalidArgumentException('Entity must be an Invoice instance');
        }
        
        if ($entity->getId()) {
            // Update existing invoice
            $this->connection->execute(
                "UPDATE {$this->table} SET invoice_date = ?, customer_id = ?, grand_total = ? WHERE id = ?",
                [$entity->getDate()->format('Y-m-d'), $entity->getCustomer()->getId(), $entity->getGrandTotal(), $entity->getId()]
            );
            return $entity;
        } else {
            // Create new invoice
            $this->connection->execute(
                "INSERT INTO {$this->table} (invoice_date, customer_id, grand_total) VALUES (?, ?, ?)",
                [$entity->getDate()->format('Y-m-d'), $entity->getCustomer()->getId(), $entity->getGrandTotal()]
            );
            
            $id = $this->connection->getPdo()->lastInsertId();
            $entity->setId($id);
            return $entity;
        }
    }
    
    protected function createEntityFromData(array $data): object
    {
        // This is a simplified implementation - you might need to load customer and items
        $customer = new \AnwarSaeed\InvoiceProcessor\Models\Customer($data['customer_id'], '', '');
        return new Invoice($data['id'], new \DateTime($data['invoice_date']), $customer, $data['grand_total']);
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