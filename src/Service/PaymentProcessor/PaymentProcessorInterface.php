<?php

namespace App\Service\PaymentProcessor;

interface PaymentProcessorInterface
{
    public function processPayment(float $amount): bool;
}