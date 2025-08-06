<?php
namespace AnwarSaeed\InvoiceProcessor\Models;

class InvoiceItem
{
    private ?int $id;
    private Invoice $invoice;
    private Product $product;
    private int $quantity;
    private float $total;

    public function __construct(?int $id, Invoice $invoice, Product $product, int $quantity, float $total)
    {
        $this->id = $id;
        $this->invoice = $invoice;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->total = $total;
    }
    //Gatters
    /**
     * Gets the unique identifier of the invoice item.
     *
     * @return int|null if the invoice item is not persisted, this value will be null
     */
    public function getId(): ?int { return $this->id; }

    /**
     * Gets the invoice of the invoice item.
     *
     * @return Invoice the invoice of the invoice item
     */
    public function getInvoice(): Invoice { return $this->invoice; }

    /**
     * Gets the product of the invoice item.
     *
     * @return Product the product of the invoice item
     */
    public function getProduct(): Product { return $this->product; }

    /**
     * Gets the quantity of the invoice item.
     *
     * @return int the quantity of the invoice item
     */
    public function getQuantity(): int { return $this->quantity; }

    /**
     * Gets the total of the invoice item.
     *
     * @return float the total of the invoice item
     */
    public function getTotal(): float { return $this->total; }

    //Setters
    /**
     * Sets the quantity of the invoice item.
     *
     * @param int $quantity the quantity of the invoice item
     */
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }

    /**
     * Sets the total of the invoice item.
     *
     * @param float $total the total of the invoice item
     */
    public function setTotal(float $total): void { $this->total = $total; }

    /**
     * Sets the invoice of the invoice item.
     *
     * @param Invoice $invoice the invoice of the invoice item
     */
    public function setInvoice(Invoice $invoice): void { $this->invoice = $invoice; }

    /**
     * Sets the product of the invoice item.
     *
     * @param Product $product the product of the invoice item
     */
    public function setProduct(Product $product): void { $this->product = $product; }

    /**
     * Sets the unique identifier of the invoice item.
     *
     * @param int $id the unique identifier of the invoice item
     */
    public function setId(int $id): void { $this->id = $id; }    
}