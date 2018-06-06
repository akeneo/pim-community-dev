<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Clients;

use GuzzleHttp\ClientInterface;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Client;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\UriGenerator;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\ApiResponse;

class Webservice implements Client
{
    private $uriGenerator;
    
    private $httpClient;

    public function __construct(UriGenerator $uriGenerator, ClientInterface $httpClient)
    {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

    public function getResource(string $uri, array $uriParameters): ApiResponse
    {
        $route = $this->uriGenerator->generate($uri, $uriParameters);

        $response = $this->httpClient->request('GET', $route);

        return new ApiResponse(
            $response->getStatusCode(),
            json_decode($response->getBody()->getContents(), true)
        );
    }

    public function createResource(string $uri, array $uriParameters = [], array $body = []): ApiResponse
    {
        $route = $this->uriGenerator->generate($uri, $uriParameters);

        $response = $this->httpClient->request('POST', $route, $body);

        return new ApiResponse(
            $response->getStatusCode(),
            json_decode($response->getBody()->getContents(), true)
        );
    }
}
