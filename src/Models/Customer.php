<?php
namespace AnwarSaeed\InvoiceProcessor\Models;

class Customer
{
    private ?int $id;
    private string $name;
    private string $address;

    public function __construct(?int $id, string $name, string $address)
    {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
    }


    /**
     * Gets the unique identifier of the customer.
     *
     * @return int|null if the customer is not persisted, this value will be null
     */
    public function getId(): ?int { return $this->id; }

    /**
     * Gets the name of the customer.
     *
     * @return string the name of the customer
     */
    public function getName(): string { return $this->name; }

    /**
     * Gets the address of the customer.
     *
     * @return string the address of the customer
     */
    public function getAddress(): string { return $this->address; }

    //Setters
    /**
     * Sets the name of the customer.
     *
     * @param string $name the name of the customer
     */
    public function setName(string $name): void { $this->name = $name; }    

    /**
     * Sets the address of the customer.
     *
     * @param string $address the address of the customer
     */
    public function setAddress(string $address): void { $this->address = $address; }

    /**
     * Sets the unique identifier of the customer.
     *
     * @param int $id the unique identifier of the customer
     */
    public function setId(int $id): void { $this->id = $id; }


}