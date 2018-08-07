<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;

class SubscriptionWebservice implements SubscriptionApiInterface
{
    private $uriGenerator;

    private $httpClient;

    public function __construct(UriGenerator $uriGenerator, Client $httpClient)
    {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProduct(array $identifiers): ApiResponse
    {
        $route = $this->uriGenerator->generate('/subscriptions');

        $response = $this->httpClient->request('POST', $route, [
            'form_params' => [$identifiers],
        ]);

        return new ApiResponse(
            $response->getStatusCode(),
            new SubscriptionCollection(json_decode($response->getBody()->getContents(), true))
        );
    }
}
