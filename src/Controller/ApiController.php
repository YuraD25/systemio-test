<?php

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Service\PaymentProcessor\PaymentProcessorFactory;
use App\Service\PriceCalculatorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiController extends AbstractController
{
    public function __construct(
        private PriceCalculatorService $priceCalculatorService,
    ) {
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

            $paymentProcessorType = $request->paymentProcessor;

            $paymentProcessor = paymentProcessorFactory::create($paymentProcessorType);
            $result = $paymentProcessor->processPayment($price);

            if (!$result) {
                return $this->json(['status' => 'error', 'message' => 'Payment processing failed.'],
                    Response::HTTP_BAD_REQUEST);
            }
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Payment processing error: ' . $e->getMessage()],
                Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['message' => 'Purchase successful', 'paid' => $price]);
    }
}

