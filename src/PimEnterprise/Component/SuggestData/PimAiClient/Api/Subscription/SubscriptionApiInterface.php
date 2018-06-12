<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\PimAiClient\Api\Subscription;

use PimEnterprise\Component\SuggestData\PimAiClient\Api\ApiResponse;
use PimEnterprise\Component\SuggestData\Product\ProductCode;
use PimEnterprise\Component\SuggestData\Product\ProductCodeCollection;

interface SubscriptionApiInterface
{
    /**
     * @param ProductCode $productCode
     * @return ApiResponse
     */
    public function subscribeProduct(ProductCode $productCode): ApiResponse;

    /**
     * @param ProductCodeCollection $productCodeCollection
     * @return ApiResponse
     */
    public function subscribeProducts(ProductCodeCollection $productCodeCollection): ApiResponse;
}
