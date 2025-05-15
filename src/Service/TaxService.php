<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TaxService
{
    private array $taxRates = [
        'DE' => 19,
        'IT' => 22,
        'FR' => 20,
        'GR' => 24,
    ];

    private array $taxNumberPatterns = [
        'DE' => '/^DE[0-9]{9}$/',
        'IT' => '/^IT[0-9]{11}$/',
        'GR' => '/^GR[0-9]{9}$/',
        'FR' => '/^FR[A-Z]{2}[0-9]{9}$/',
    ];

    public function getTaxRateForCountry(string $countryCode): float
    {
        $upperCountryCode = strtoupper($countryCode);

        if (!isset($this->taxRates[$upperCountryCode])) {
            throw new BadRequestHttpException(sprintf('Tax rate for country %s not found.', $upperCountryCode));
        }

        return (float)$this->taxRates[$upperCountryCode];
    }

    public function validateTaxNumberAndGetCountry(string $taxNumber): string
    {
        $countryCode = strtoupper(substr($taxNumber, 0, 2));

        if (!isset($this->taxNumberPatterns[$countryCode])) {
            throw new BadRequestHttpException(sprintf('Tax number format for country %s is not supported or country code is invalid.', $countryCode));
        }

        if (!preg_match($this->taxNumberPatterns[$countryCode], $taxNumber)) {
            throw new BadRequestHttpException(sprintf('Invalid tax number format for %s: %s. Expected format: %s', $countryCode, $taxNumber, $this->getExampleFormat($countryCode)));
        }

        return $countryCode;
    }

    private function getExampleFormat(string $countryCode): string
    {
        switch ($countryCode) {
            case 'DE':
                return 'DEXXXXXXXXX';
            case 'IT':
                return 'ITXXXXXXXXXXX';
            case 'GR':
                return 'GRXXXXXXXXX';
            case 'FR':
                return 'FRYYXXXXXXXXX';
            default:
                return 'Unknown';
        }
    }

    public function calculateTax(float $price, string $countryCode): float
    {
        $rate = $this->getTaxRateForCountry($countryCode);
        return $price * ($rate / 100);
    }
}

