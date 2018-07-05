<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Component\PimAiClient\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Component\PimAiClient\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Component\Product\ProductCode;
use Akeneo\Pim\Automation\SuggestData\Component\Product\ProductCodeCollection;

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
