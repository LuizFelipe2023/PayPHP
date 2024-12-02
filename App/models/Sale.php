<?php

namespace models;

class Sale {

    public $id;
    private $products = [];  
    public $total_price = 0;

    public function addProduct(Product $product, $quantity): void
    {
        $this->products[] = ['product' => $product, 'quantity' => $quantity];
        $this->updateTotalPrice();
    }

    private function updateTotalPrice(): void
    {
        $this->total_price = 0;
        foreach ($this->products as $productData) {
            $this->total_price += $productData['product']->getPrice() * $productData['quantity'];
        }
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getTotalPrice(): float
    {
        return $this->total_price;
    }

    public function setTotalPrice(float $price): void
    {
        $this->total_price = $price;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getProductList(): array
    {
        return $this->products;
    }

    public function setProducts(array $products): void
    {
        $this->products = $products;
        $this->updateTotalPrice(); 
    }
}
