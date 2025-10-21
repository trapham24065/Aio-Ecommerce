<?php

namespace App\Dto;

class CustomerInput
{
    public string $first_name;
    public string $last_name;
    public string $email;
    public ?string $phone = null;
}