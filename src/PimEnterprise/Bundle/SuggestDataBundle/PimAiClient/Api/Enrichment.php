<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api;

use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Client;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\ApiResponse;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription\Request;

class Enrichment
{
    private
        $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function sendSubscriptionRequest(Request $subscriptionRequest): ApiResponse
    {
        return $this->client->createResource('/enrichments', [], $subscriptionRequest->toArray());
    }
}
