<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;

final class SubscriptionFake implements SubscriptionApiInterface
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProduct(array $identifiers): ApiResponse
    {
        $filename = sprintf('subscribe-%s-%s.json', key($identifiers), current($identifiers));

        return new ApiResponse(
            200,
            new SubscriptionCollection(json_decode(
                file_get_contents(
                    sprintf(__DIR__ .'/../resources/%s', $filename)
                ), true))
        );
    }

    public function fetchProducts(): array
    {
        // TODO: Implement fetchProducts() method.
    }
}
