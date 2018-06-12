<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\PimAiClient\Api\Enrichment;

use PimEnterprise\Component\SuggestData\PimAiClient\Api\ApiResponse;
use PimEnterprise\Component\SuggestData\PimAiClient\Api\SubscriptionId;

interface EnrichmentApiInterface
{
    /**
     * @param SubscriptionId $subcriptionId
     * @return ApiResponse
     */
    public function getEnrichedData(SubscriptionId $subcriptionId): ApiResponse;
}
