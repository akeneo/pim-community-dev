<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api;

use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\ApiResponse;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription\ProductCode;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription\ProductCodeCollection;

interface EnrichmentApi
{
    public function subscribeProduct(ProductCode $productCode): ApiResponse;
    
    public function subscribeProducts(ProductCodeCollection $productCodeCollection): ApiResponse;
}
