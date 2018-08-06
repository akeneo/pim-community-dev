<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\ProductCode;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\ProductCodeCollection;

final class SubscriptionFake implements SubscriptionApiInterface
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProduct(ProductCode $productCode): ApiResponse
    {
        $filename = sprintf('subscribe-%s-%s.json', $productCode->identifierName(), $productCode->value());

        return new ApiResponse(
            200,
            json_decode(
                file_get_contents(
                    sprintf(__DIR__ .'/../resources/%s', $filename)
                ), true)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProducts(ProductCodeCollection $productCodeCollection): ApiResponse
    {
        throw new \LogicException('Not yet implemented');
    }
}
