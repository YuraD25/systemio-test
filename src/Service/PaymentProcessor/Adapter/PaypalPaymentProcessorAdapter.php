<?php

namespace App\Service\PaymentProcessor\Adapter;

use App\Service\PaymentProcessor\PaymentProcessorInterface;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor as ExternalPaypalPaymentProcessor;

readonly class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private ExternalPaypalPaymentProcessor $paypalPaymentProcessor,
    )
    {}

    public function processPayment(float $amount): bool
    {
        try {
            $this->paypalPaymentProcessor->pay((int)$amount);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}