<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Clients;

use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Client;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\UriGenerator;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class Memory implements Client
{
    private
        $uriGenerator,
        $fixtures;

    public function __construct(UriGenerator $uriGenerator)
    {
        $this->uriGenerator = $uriGenerator;
        $this->fixtures = $this->buildFixtures();
    }

    public function getResource(string $uri, array $uriParameters): ApiResponse
    {
        $route = $this->uriGenerator->generate($uri, $uriParameters);

        return $this->getResponse($route);
    }

    public function createResource(string $uri, array $uriParameters = [], array $body = []): ApiResponse
    {
        $route = $this->uriGenerator->generate($uri, $uriParameters);

        return $this->getResponse($route);
    }

    private function getResponse(string $route): ApiResponse
    {
        if(! array_key_exists($route, $this->fixtures))
        {
            throw new \Exception(sprintf('Route %s not found', $route));
        }

        return new ApiResponse(Response::HTTP_OK, $this->fixtures[$route]);
    }

    private function buildFixtures(): array
    {
        return [
            'http://localhost:8080/subscription/35c1c788-bef2-4024-8366-237145703fef' => json_decode(
                file_get_contents(__DIR__ . '/../docs/subscription/35c1c788-bef2-4024-8366-237145703fef'),
                true
            ),
            'http://localhost:8080/enrichments' => json_decode(
                file_get_contents(__DIR__ . '/../docs/enrichments'),
                true
            ),
        ];
    }
}
