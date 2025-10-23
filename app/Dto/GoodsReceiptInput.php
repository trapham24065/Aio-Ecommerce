<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

// Custom validator for warehouse_id/supplier_id

class GoodsReceiptInput
{

    #[Assert\NotBlank(message: "Warehouse ID cannot be empty.")]
    #[Assert\Type("integer")]
    // Optional: Add custom validation to ensure the ID exists in the 'warehouses' table
        // #[ExistsInDatabase(table: 'warehouses', column: 'id')]
    public int $warehouse_id;

    #[Assert\Type("integer")]
    // Optional: Add custom validation to ensure the ID exists in the 'suppliers' table
        // #[ExistsInDatabase(table: 'suppliers', column: 'id')]
    public ?int $supplier_id = null; // Optional

    #[Assert\NotBlank(message: "Receipt date cannot be empty.")]
    #[Assert\Date(message: "Receipt date must be a valid date (YYYY-MM-DD).")]
    public string $receipt_date; // Keep as string for input, validation handles format

    #[Assert\Type("string")]
    public ?string $notes = null; // Optional

    /**
     * @var GoodsReceiptItemInput[]
     */
    #[Assert\NotBlank(message: "Items cannot be empty.")]
    #[Assert\Type("array")]
    #[Assert\Count(min: 1, minMessage: "You must add at least one item.")]
    #[Assert\Valid] // This tells the validator to also validate each GoodsReceiptItemInput object inside the array
    public array $items = [];

}
