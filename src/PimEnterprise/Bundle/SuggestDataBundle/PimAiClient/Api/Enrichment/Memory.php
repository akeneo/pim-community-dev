<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api\Enrichment;

use PimEnterprise\Bundle\SuggestDataBundle\Infra\Fake\FakeHALProducts;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api\EnrichmentApi;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\ApiResponse;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription\ProductCode;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription\ProductCodeCollection;
use Symfony\Component\HttpFoundation\Response;

class Memory implements EnrichmentApi
{
    private $fakeHALProducts;

    public function __construct()
    {
        $this->fakeHALProducts = new FakeHALProducts();
    }

    public function subscribeProduct(ProductCode $productCode): ApiResponse
    {
        $hal = $this->fakeHALProducts->addProduct($productCode->value())->getFakeHAL();
         
        return new ApiResponse(
             Response::HTTP_OK,
             json_decode($hal, true)
         );
    }
    
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
