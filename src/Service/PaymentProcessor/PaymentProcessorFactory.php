<?php

namespace App\Service\PaymentProcessor;

use App\Service\PaymentProcessor\Adapter\PaypalPaymentProcessorAdapter;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use App\Service\PaymentProcessor\Adapter\StripePaymentProcessorAdapter;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class PaymentProcessorFactory
{
    public static function create(string $paymentProcessorType): PaymentProcessorInterface
    {
        switch ($paymentProcessorType) {
            case 'paypal':
                return new PaypalPaymentProcessorAdapter(new PaypalPaymentProcessor());
            case 'stripe':
                return new StripePaymentProcessorAdapter(new StripePaymentProcessor());
            default:
                throw new \InvalidArgumentException("Unknown payment processor type: {$paymentProcessorType}");
        }
    }
}