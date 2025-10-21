<?php

namespace App\Dto;

class WarehouseInput
{
    public string $name;
    public ?string $code = null;
    public ?string $street = null;
    public string $city;
    public ?string $state = null;
    public ?string $postal_code = null;
    public ?string $postalCode = null; // For API compatibility
    public string $country;
    public ?bool $status = true;
}