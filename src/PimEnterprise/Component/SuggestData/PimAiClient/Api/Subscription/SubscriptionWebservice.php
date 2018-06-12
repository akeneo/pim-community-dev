<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\PimAiClient\Api\Subscription;

use GuzzleHttp\ClientInterface;
use PimEnterprise\Component\SuggestData\PimAiClient\UriGenerator;
use PimEnterprise\Component\SuggestData\PimAiClient\Api\ApiResponse;
use PimEnterprise\Component\SuggestData\Product\ProductCode;
use PimEnterprise\Component\SuggestData\Product\ProductCodeCollection;

class SubscriptionWebservice implements SubscriptionApiInterface
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
    public function subscribeProduct(ProductCode $productCode): ApiResponse
    {
        $route = $this->uriGenerator->generate('/enrichments');

        $response = $this->httpClient->request('POST', $route, [
            $productCode->identifierName() => [$productCode->value()]
        ]);

        return new ApiResponse(
            $response->getStatusCode(),
            json_decode($response->getBody()->getContents(), true)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProducts(ProductCodeCollection $productCodeCollection): ApiResponse
    {
        $route = $this->uriGenerator->generate('/enrichments');

        $response = $this->httpClient->request('POST', $route, $productCodeCollection->toArray());

        return new ApiResponse(
            $response->getStatusCode(),
            json_decode($response->getBody()->getContents(), true)
        );
    }
}
