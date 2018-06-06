<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient;

use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api\Enrichment;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api\Subscription;

class PimAiApi
{
    private $enrichmentApi;
    
    private $subscriptionApi;

    public function __construct(Enrichment $enrichmentApi, Subscription $subscriptionApi)
    {
        $this->enrichmentApi = $enrichmentApi;
        $this->subscriptionApi = $subscriptionApi;
    }

    public function getSubscriptionApi(): Subscription
    {
        return $this->subscriptionApi;
    }

    public function getEnrichmentApi(): Enrichment
    {
        return $this->enrichmentApi;
    }
}
