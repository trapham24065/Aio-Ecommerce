<?php

namespace App\Dto;

class CategoryInput
{
    public string $name;
    public ?string $code = null;
    public ?bool $status = true;
}