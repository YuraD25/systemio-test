<?php

namespace App\DataFixtures;

use App\Entity\Coupon;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['name' => 'Iphone', 'price' => 100.00],
            ['name' => 'Наушники', 'price' => 20.00],
            ['name' => 'Чехол', 'price' => 10.00],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setPrice($productData['price']);
            $manager->persist($product);
        }

        $coupons = [
            ['code' => 'FIXED10', 'type' => 'fixed', 'value' => 10],
            ['code' => 'PERCENT10', 'type' => 'percentage', 'value' => 10],
        ];

        foreach ($coupons as $couponData) {
            $coupon = new Coupon();
            $coupon->setCode($couponData['code']);
            $coupon->setType($couponData['type']);
            $coupon->setValue($couponData['value']);
            $manager->persist($coupon);
        }

        $manager->flush();
    }
}
