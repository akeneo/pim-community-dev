<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\Enrichment;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\SubscriptionId;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\UriGenerator;
use GuzzleHttp\ClientInterface;

class EnrichmentWebservice implements EnrichmentApiInterface
{
    private $uriGenerator;

    private $httpClient;

    public function __construct(UriGenerator $uriGenerator, ClientInterface $httpClient)
    {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnrichedData(SubscriptionId $subcriptionId): ApiResponse
    {
        $route = $this->uriGenerator->generate('/subscription/%s', [$subcriptionId->value()]);

        $response = $this->httpClient->request('GET', $route);

        return new ApiResponse(
            $response->getStatusCode(),
            json_decode($response->getBody()->getContents(), true)
        );
    }
}
