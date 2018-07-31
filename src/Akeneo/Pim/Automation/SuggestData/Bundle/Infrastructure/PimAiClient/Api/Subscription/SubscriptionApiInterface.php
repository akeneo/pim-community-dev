<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\ValueObject\ProductCode;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\ValueObject\ProductCodeCollection;

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
