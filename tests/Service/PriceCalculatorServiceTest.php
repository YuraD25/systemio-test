<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Entity\Coupon;
use App\Repository\ProductRepository;
use App\Repository\CouponRepository;
use App\Service\PriceCalculatorService;
use App\Service\TaxService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PriceCalculatorServiceTest extends TestCase
{
    private ProductRepository $productRepositoryMock;
    private CouponRepository $couponRepositoryMock;
    private TaxService $taxServiceMock;
    private PriceCalculatorService $priceCalculatorService;

    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->couponRepositoryMock = $this->createMock(CouponRepository::class);
        $this->taxServiceMock = $this->createMock(TaxService::class);

        $this->priceCalculatorService = new PriceCalculatorService(
            $this->productRepositoryMock,
            $this->couponRepositoryMock,
            $this->taxServiceMock
        );
    }

    public function testCalculatePriceHappyPath(): void
    {
        $product = new Product();
        $product->setPrice(100.0);

        $this->productRepositoryMock->method("find")->willReturn($product);
        $this->taxServiceMock->method("validateTaxNumberAndGetCountry")->willReturn("DE");
        $this->taxServiceMock->method("calculateTax")->willReturn(19.0); // 19% of 100

        $finalPrice = $this->priceCalculatorService->calculatePrice(1, "DE123456789", null);
        $this->assertEquals(119.0, $finalPrice);
    }

    public function testCalculatePriceWithPercentageCoupon(): void
    {
        $product = new Product();
        $product->setPrice(100.0);

        $coupon = new Coupon();
        $coupon->setType("percentage");
        $coupon->setValue(10.0); // 10% discount

        $this->productRepositoryMock->method("find")->willReturn($product);
        $this->couponRepositoryMock->method("findOneBy")->willReturn($coupon);
        $this->taxServiceMock->method("validateTaxNumberAndGetCountry")->willReturn("DE");
        // Price after 10% coupon: 100 - 10 = 90. Tax: 19% of 90 = 17.1
        $this->taxServiceMock->method("calculateTax")->willReturn(17.1);

        $finalPrice = $this->priceCalculatorService->calculatePrice(1, "DE123456789", "D10");
        // Expected: 90 + 17.1 = 107.1
        $this->assertEquals(107.1, $finalPrice);
    }

    public function testCalculatePriceWithFixedCoupon(): void
    {
        $product = new Product();
        $product->setPrice(100.0);

        $coupon = new Coupon();
        $coupon->setType("fixed");
        $coupon->setValue(20.0); // 20 fixed discount

        $this->productRepositoryMock->method("find")->willReturn($product);
        $this->couponRepositoryMock->method("findOneBy")->willReturn($coupon);
        $this->taxServiceMock->method("validateTaxNumberAndGetCountry")->willReturn("DE");
        // Price after 20 fixed coupon: 100 - 20 = 80. Tax: 19% of 80 = 15.2
        $this->taxServiceMock->method("calculateTax")->willReturn(15.2);

        $finalPrice = $this->priceCalculatorService->calculatePrice(1, "DE123456789", "F20");
        // Expected: 80 + 15.2 = 95.2
        $this->assertEquals(95.2, $finalPrice);
    }

    public function testCalculatePriceProductNotFound(): void
    {
        $this->productRepositoryMock->method("find")->willReturn(null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage("Product with ID 999 not found.");
        $this->priceCalculatorService->calculatePrice(999, "DE123456789", null);
    }

    public function testCalculatePriceCouponNotFound(): void
    {
        $product = new Product();
        $product->setPrice(100.0);

        $this->productRepositoryMock->method("find")->willReturn($product);
        $this->couponRepositoryMock->method("findOneBy")->willReturn(null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage("Coupon code \"INVALID\" not found.");
        $this->priceCalculatorService->calculatePrice(1, "DE123456789", "INVALID");
    }

    public function testCalculatePriceWithFixedCouponMakingPriceNegative(): void
    {
        $product = new Product();
        $product->setPrice(10.0);

        $coupon = new Coupon();
        $coupon->setType("fixed");
        $coupon->setValue(20.0); // 20 fixed discount, product price is 10

        $this->productRepositoryMock->method("find")->willReturn($product);
        $this->couponRepositoryMock->method("findOneBy")->willReturn($coupon);
        $this->taxServiceMock->method("validateTaxNumberAndGetCountry")->willReturn("DE");
        // Price after coupon: 0 (cannot be negative). Tax: 19% of 0 = 0
        $this->taxServiceMock->method("calculateTax")->with(0.0, "DE")->willReturn(0.0);

        $finalPrice = $this->priceCalculatorService->calculatePrice(1, "DE123456789", "F20");
        // Expected: 0 + 0 = 0
        $this->assertEquals(0.0, $finalPrice);
    }
}

