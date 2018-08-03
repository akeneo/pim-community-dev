<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\ProductCode;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\ProductCodeCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Fake\FakeHALProducts;

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
        throw new \LogicException('Not yet implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProducts(ProductCodeCollection $productCodeCollection): ApiResponse
    {
        throw new \LogicException('Not yet implemented');
    }
}
