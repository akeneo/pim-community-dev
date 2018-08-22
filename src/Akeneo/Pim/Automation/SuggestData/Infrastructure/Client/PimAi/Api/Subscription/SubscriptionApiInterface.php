<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;

interface SubscriptionApiInterface
{
    /**
     * @param array $identifiers
     *
     * @return ApiResponse
     */
    public function subscribeProduct(array $identifiers): ApiResponse;

    public function fetchProducts(): array;
}
