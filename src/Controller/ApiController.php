<?php

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Service\PriceCalculatorService;
use Symfony\Component\HttpFoundation\Response;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiController extends AbstractController
{
    private PriceCalculatorService $priceCalculatorService;
    private PaypalPaymentProcessor $paypalPaymentProcessor;
    private StripePaymentProcessor $stripePaymentProcessor;

    public function __construct(
        PriceCalculatorService $priceCalculatorService,
        PaypalPaymentProcessor $paypalPaymentProcessor,
        StripePaymentProcessor $stripePaymentProcessor
    ) {
        $this->priceCalculatorService = $priceCalculatorService;
        $this->paypalPaymentProcessor = $paypalPaymentProcessor;
        $this->stripePaymentProcessor = $stripePaymentProcessor;
    }

    #[Route("/calculate-price", name: "calculate_price", methods: ["POST"])]
    public function calculatePrice(#[MapRequestPayload] CalculatePriceRequest $request): JsonResponse
    {
        try {
            $price = $this->priceCalculatorService->calculatePrice(
                $request->product,
                $request->taxNumber,
                $request->couponCode
            );
            return $this->json(['price' => $price]);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route("/purchase", name: "purchase", methods: ["POST"])]
    public function purchase(#[MapRequestPayload] PurchaseRequest $request): JsonResponse
    {
        try {
            $price = $this->priceCalculatorService->calculatePrice(
                $request->product,
                $request->taxNumber,
                $request->couponCode
            );

            if ($request->paymentProcessor === "paypal") {
                // Bcoz you know, paypal expects price in cents.
                $this->paypalPaymentProcessor->pay((int)($price * 100));
            } elseif ($request->paymentProcessor === "stripe") {
                if (!$this->stripePaymentProcessor->processPayment($price)) {
                    return $this->json(['error' => 'Payment failed'], Response::HTTP_BAD_REQUEST);
                }
            }

        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Payment processing error: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['message' => 'Purchase successful', 'paid' => $price]);
    }
}

