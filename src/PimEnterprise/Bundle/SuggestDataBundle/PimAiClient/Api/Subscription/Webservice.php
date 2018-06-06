<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api\Subscription;

use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Client;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\ApiResponse;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\SubscriptionId;

class Webservice
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getSubscription(SubscriptionId $subcriptionId): ApiResponse
    {
        return $this->client->getResource('/subscription/%s', [$subcriptionId->value()]);
    }
}
