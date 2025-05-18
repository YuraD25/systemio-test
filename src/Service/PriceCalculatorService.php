<?php

namespace App\Service;

use App\Repository\ProductRepository;
use App\Repository\CouponRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class PriceCalculatorService
{
    public function __construct(
        private ProductRepository $productRepository,
        private CouponRepository $couponRepository,
        private TaxService $taxService
    ) {
    }

    public function calculatePrice(int $productId, string $taxNumber, ?string $couponCode): float
    {
        $product = $this->productRepository->find($productId);

        if (!$product) {
            throw new BadRequestHttpException(sprintf("Product with ID %d not found.", $productId));
        }

        $basePrice = $product->getPrice();

        $discountedPrice = $basePrice;

        if ($couponCode) {
            $coupon = $this->couponRepository->findOneBy(["code" => $couponCode]);

            if (!$coupon) {
                throw new BadRequestHttpException(sprintf('Coupon code "%s" not found.', $couponCode));
            }

            if ($coupon->getType() === "percentage") {
                $discountedPrice = $basePrice - ($basePrice * ($coupon->getValue() / 100));
            } elseif ($coupon->getType() === "fixed") {
                $discountedPrice = $basePrice - $coupon->getValue();

                if ($discountedPrice < 0) {
                    $discountedPrice = 0;
                }
            }
        }

        $countryCode = $this->taxService->validateTaxNumberAndGetCountry($taxNumber);

        $taxAmount = $this->taxService->calculateTax($discountedPrice, $countryCode);

        $finalPrice = $discountedPrice + $taxAmount;

        return round($finalPrice, 2);
    }
}

