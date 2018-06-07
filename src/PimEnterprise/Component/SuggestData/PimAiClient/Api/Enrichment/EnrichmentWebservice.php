<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\PimAiClient\Api\Enrichment;

use GuzzleHttp\ClientInterface;
use PimEnterprise\Component\SuggestData\PimAiClient\Api\ApiResponse;
use PimEnterprise\Component\SuggestData\PimAiClient\Api\SubscriptionId;
use PimEnterprise\Component\SuggestData\PimAiClient\UriGenerator;

class EnrichmentWebservice implements EnrichmentApiInterface
{
    private $uriGenerator;

    private $httpClient;

    public function __construct(UriGenerator $uriGenerator, ClientInterface $httpClient)
    {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

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
