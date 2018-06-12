<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\PimAiClient\Api\Subscription;

use PimEnterprise\Bundle\SuggestDataBundle\Infra\Fake\FakeHALProducts;
use PimEnterprise\Component\SuggestData\PimAiClient\Api\ApiResponse;
use PimEnterprise\Component\SuggestData\Product\ProductCode;
use PimEnterprise\Component\SuggestData\Product\ProductCodeCollection;
use Symfony\Component\HttpFoundation\Response;

final class Memory implements SubscriptionApiInterface
{
    private $fakeHALProducts;

    public function __construct()
    {
        $this->fakeHALProducts = new FakeHALProducts();
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProduct(ProductCode $productCode): ApiResponse
    {
        $hal = $this->fakeHALProducts->addProduct($productCode->value())->getFakeHAL();
         
        return new ApiResponse(
             Response::HTTP_OK,
             json_decode($hal, true)
         );
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProducts(ProductCodeCollection $productCodeCollection): ApiResponse
    {
        foreach ($productCodeCollection as $productCode) {
            $this->fakeHALProducts->addProduct($productCode->value());
        }
        
        $hal = $this->fakeHALProducts->getFakeHAL();
        
        return new ApiResponse(
            Response::HTTP_OK,
            json_decode($hal, true)
        );
    }
}
