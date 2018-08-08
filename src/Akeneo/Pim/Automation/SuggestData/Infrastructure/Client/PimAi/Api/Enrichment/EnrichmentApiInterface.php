<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Enrichment;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\SubscriptionId;

interface EnrichmentApiInterface
{
    /**
     * @param SubscriptionId $subcriptionId
     * @return ApiResponse
     */
    public function getEnrichedData(SubscriptionId $subcriptionId): ApiResponse;
}
