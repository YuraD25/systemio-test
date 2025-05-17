<?php

namespace App\Tests\Service;

use App\Service\TaxService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TaxServiceTest extends TestCase
{
    private TaxService $taxService;
    private ParameterBagInterface $parameterBag;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->parameterBag->method('get')
            ->with('tax_rates')
            ->willReturn([
                'DE' => 19,
                'IT' => 22,
                'GR' => 24,
                'FR' => 20,
            ]);

        $this->taxService = new TaxService($this->parameterBag);
    }

    public function testGetTaxRateForCountry()
    {
        $this->assertEquals(19, $this->taxService->getTaxRateForCountry('DE'));
        $this->assertEquals(22, $this->taxService->getTaxRateForCountry('IT'));
        $this->assertEquals(24, $this->taxService->getTaxRateForCountry('GR'));
        $this->assertEquals(20, $this->taxService->getTaxRateForCountry('FR'));
    }

    public function testGetTaxRateForCountryThrowsExceptionForInvalidCountry()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Tax rate for country US not found.');

        $this->taxService->getTaxRateForCountry('US');
    }

    public function testValidateTaxNumberAndGetCountry()
    {
        $this->assertEquals('DE', $this->taxService->validateTaxNumberAndGetCountry('DE123456789'));
        $this->assertEquals('IT', $this->taxService->validateTaxNumberAndGetCountry('IT12345678901'));
        $this->assertEquals('GR', $this->taxService->validateTaxNumberAndGetCountry('GR123456789'));
        $this->assertEquals('FR', $this->taxService->validateTaxNumberAndGetCountry('FRAB123456789'));
    }

    public function testValidateTaxNumberAndGetCountryThrowsExceptionForInvalidCountryCode()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Tax number format for country US is not supported or country code is invalid.');

        $this->taxService->validateTaxNumberAndGetCountry('US123456789');
    }

    public function testValidateTaxNumberAndGetCountryThrowsExceptionForInvalidFormat()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid tax number format for DE: DE123. Expected format: DEXXXXXXXXX');

        $this->taxService->validateTaxNumberAndGetCountry('DE123');
    }

    public function testCalculateTax()
    {
        $this->assertEquals(19.0, $this->taxService->calculateTax(100, 'DE'));
        $this->assertEquals(22.0, $this->taxService->calculateTax(100, 'IT'));
        $this->assertEquals(12.0, $this->taxService->calculateTax(50, 'GR'));
        $this->assertEquals(10.0, $this->taxService->calculateTax(50, 'FR'));
    }

    public function testCalculateTaxWithZeroPrice()
    {
        $this->assertEquals(0.0, $this->taxService->calculateTax(0, 'DE'));
    }

    public function testGetExampleFormat()
    {
        // Using reflection to test private method
        $reflector = new \ReflectionClass(TaxService::class);
        $method = $reflector->getMethod('getExampleFormat');
        $method->setAccessible(true);

        $service = new TaxService($this->parameterBag);

        $this->assertEquals('DEXXXXXXXXX', $method->invokeArgs($service, ['DE']));
        $this->assertEquals('ITXXXXXXXXXXX', $method->invokeArgs($service, ['IT']));
        $this->assertEquals('GRXXXXXXXXX', $method->invokeArgs($service, ['GR']));
        $this->assertEquals('FRYYXXXXXXXXX', $method->invokeArgs($service, ['FR']));
        $this->assertEquals('Unknown', $method->invokeArgs($service, ['XX']));
    }
}