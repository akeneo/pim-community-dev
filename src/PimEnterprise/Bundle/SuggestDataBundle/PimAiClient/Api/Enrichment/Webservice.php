<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api\Enrichment;

use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api\EnrichmentApi;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Client;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\ApiResponse;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription\ProductCode;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription\ProductCodeCollection;

class Webservice implements EnrichmentApi
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function subscribeProduct(ProductCode $productCode): ApiResponse
    {
        return $this->client->createResource('/enrichments', [], [
            $productCode->identifierName() => [$productCode->value()]
        ]);
    }
    
    public function subscribeProducts(ProductCodeCollection $productCodeCollection): ApiResponse
    {
        return $this->client->createResource('/enrichments', [], $productCodeCollection->toArray());
    }
}
