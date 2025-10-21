<?php

namespace App\Dto;

class ProductInput
{
    public string $type;
    public int $category_id;
    public ?int $supplier_id = null;
    public ?int $brand_id = null;
    public string $name;
    public ?string $sku = null;
    public ?string $description = null;
    public string $thumbnail;
    public float $base_cost;
    public ?int $quantity = 0;
    public ?int $flag = 0;
    public ?bool $status = true;
}