<?php

namespace App\Dto;

class SupplierInput
{
    public string $name;
    public ?string $code = null;
    public ?string $home_url = null;
    public ?bool $status = true;
}