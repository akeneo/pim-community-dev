<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api;

use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\ApiResponse;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\SubscriptionId;

interface SubscriptionApi
{
    public function getSubscription(SubscriptionId $subcriptionId): ApiResponse;
}
