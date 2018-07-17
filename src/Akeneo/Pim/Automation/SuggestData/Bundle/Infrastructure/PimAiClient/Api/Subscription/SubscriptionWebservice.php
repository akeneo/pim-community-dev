<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\UriGenerator;
use Akeneo\Pim\Automation\SuggestData\Component\Product\ProductCode;
use Akeneo\Pim\Automation\SuggestData\Component\Product\ProductCodeCollection;
use GuzzleHttp\ClientInterface;

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
