<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequest
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

    #[Assert\NotBlank]
    #[Assert\Type("string")]
    #[Assert\Choice(choices: ["paypal", "stripe"], message: "Invalid payment processor. Choose paypal or stripe.")]
    public ?string $paymentProcessor = null;
}

