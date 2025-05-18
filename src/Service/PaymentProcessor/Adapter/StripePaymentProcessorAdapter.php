<?php

namespace App\Service\PaymentProcessor\Adapter;

use App\Service\PaymentProcessor\PaymentProcessorInterface;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor as ExternalStripePaymentProcessor;

readonly class StripePaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private ExternalStripePaymentProcessor $stripePaymentProcessor,
    ) {}

    public function processPayment(float $amount): bool
    {
        return $this->stripePaymentProcessor->processPayment((int)$amount * 100);
    }
}