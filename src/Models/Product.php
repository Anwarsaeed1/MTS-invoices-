<?php
namespace AnwarSaeed\InvoiceProcessor\Models;

class Product
{
    private ?int $id;
    private string $name;
    private float $price;

    public function __construct(?int $id, string $name, float $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * Gets the unique identifier of the product.
     *
     * @return int|null if the product is not persisted, this value will be null
     */
    public function getId(): ?int { return $this->id; }

    /**
     * Gets the name of the product.
     *
     * @return string the name of the product
     */
    public function getName(): string { return $this->name; }

    /**
     * Gets the price of the product.
     *
     * @return float the price of the product
     */
    public function getPrice(): float { return $this->price; }


    //Setters

    /**
     * Sets the price of the product.
     *
     * @param float $price the price of the product
     */
    public function setPrice(float $price): void { $this->price = $price; }

    /**
     * Sets the name of the product.    
     *
     * @param string $name the name of the product
     */
    public function setName(string $name): void { $this->name = $name; }

    /**
     * Sets the unique identifier of the product.
     *
     * @param int $id the unique identifier of the product  
     */
    public function setId(int $id): void { $this->id = $id; }


}