<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CalculatePriceRequest
{
    #[Assert\NotBlank]
    #[Assert\Type("integer")]
    #[Assert\Positive]
    public ?int $product = null;

    #[Assert\NotBlank]
    #[Assert\Type("string")]
    public ?string $taxNumber = null;

    #[Assert\Type("string")]
    public ?string $couponCode = null;
}

