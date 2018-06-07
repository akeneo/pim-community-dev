<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\PimAiClient\Api\Subscription;

use PimEnterprise\Component\SuggestData\PimAiClient\Api\ApiResponse;
use PimEnterprise\Component\SuggestData\Product\ProductCode;
use PimEnterprise\Component\SuggestData\Product\ProductCodeCollection;

interface SubscriptionApiInterface
{
    public function subscribeProduct(ProductCode $productCode): ApiResponse;
    
    public function subscribeProducts(ProductCodeCollection $productCodeCollection): ApiResponse;
}
