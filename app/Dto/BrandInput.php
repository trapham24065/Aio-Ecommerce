<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class BrandInput
{

    #[Assert\NotBlank(message: "The name field is required.")]
    #[Assert\Type("string")]
    #[Assert\Length(max: 100)]
    public string $name;

    #[Assert\Type("string")]
    #[Assert\Length(max: 100)]
    public ?string $code = null;

    #[Assert\Type("bool")]
    public ?bool $status = true;

}

