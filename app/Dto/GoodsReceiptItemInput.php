<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class GoodsReceiptItemInput
{

    #[Assert\NotBlank(message: "Product SKU cannot be empty.")]
    #[Assert\Type("string")]
    public string $product_variant_sku;

    #[Assert\NotBlank(message: "Quantity cannot be empty.")]
    #[Assert\Type("integer")]
    #[Assert\GreaterThan(0, message: "Quantity must be greater than 0.")]
    public int $quantity;

}
