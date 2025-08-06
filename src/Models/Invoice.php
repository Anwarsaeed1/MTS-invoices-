<?php
namespace AnwarSaeed\InvoiceProcessor\Models;

use DateTime;

class Invoice
{
    private ?int $id;
    private DateTime $date;
    private Customer $customer;
    private float $grandTotal;
    private array $items = [];

    public function __construct(?int $id, DateTime $date, Customer $customer, float $grandTotal)
    {
        $this->id = $id;
        $this->date = $date;
        $this->customer = $customer;
        $this->grandTotal = $grandTotal;
    }

    public function addItem(InvoiceItem $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Gets the unique identifier of the invoice.
     *
     * @return int|null if the invoice is not persisted, this value will be null
     */
    public function getId(): ?int { return $this->id; }

    /**
     * Gets the date of the invoice.
     *
     * @return DateTime the date of the invoice
     */
    public function getDate(): DateTime { return $this->date; }

    /**
     * Gets the customer of the invoice.
     *
     * @return Customer the customer of the invoice
     */
    public function getCustomer(): Customer { return $this->customer; }

    /**
     * Gets the grand total of the invoice.
     *
     * @return float the grand total of the invoice
     */
    public function getGrandTotal(): float { return $this->grandTotal; }

    /**
     * Gets the items of the invoice.
     *
     * @return array the items of the invoice
     */
    public function getItems(): array { return $this->items; }


    //Setters

    /**
     * Sets the unique identifier of the invoice.
     *
     * @param int $id the unique identifier of the invoice
     */
    public function setId(int $id): void { $this->id = $id; }

    /**
     * Sets the date of the invoice.    
     *
     * @param DateTime $date the date of the invoice
     */
    public function setDate(DateTime $date): void { $this->date = $date; }

    /**
     * Sets the customer of the invoice.
     *
     * @param Customer $customer the customer of the invoice    
     */
    public function setCustomer(Customer $customer): void { $this->customer = $customer; }

    /**
     * Sets the grand total of the invoice.
     *
     * @param float $grandTotal the grand total of the invoice
     */
    public function setGrandTotal(float $grandTotal): void { $this->grandTotal = $grandTotal; }
}