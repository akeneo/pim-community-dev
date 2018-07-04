<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Component\PimAiClient\Api\Enrichment;

use Akeneo\Pim\Automation\SuggestData\Component\PimAiClient\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Component\PimAiClient\Api\SubscriptionId;

interface EnrichmentApiInterface
{
    /**
     * @param SubscriptionId $subcriptionId
     * @return ApiResponse
     */
    public function getEnrichedData(SubscriptionId $subcriptionId): ApiResponse;
}
