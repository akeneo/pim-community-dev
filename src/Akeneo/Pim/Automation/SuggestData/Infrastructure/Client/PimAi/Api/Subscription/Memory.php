<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Fake\FakeHALProducts;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\ProductCode;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\ProductCodeCollection;
use Symfony\Component\HttpFoundation\Response;

final class Memory implements SubscriptionApiInterface
{
    private $fakeHALProducts;

    public function __construct()
    {
        $this->fakeHALProducts = new FakeHALProducts();
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProduct(ProductCode $productCode): ApiResponse
    {
        $hal = $this->fakeHALProducts->addProduct($productCode->value())->getFakeHAL();

        return new ApiResponse(
             Response::HTTP_OK,
             json_decode($hal, true)
         );
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProducts(ProductCodeCollection $productCodeCollection): ApiResponse
    {
        foreach ($productCodeCollection as $productCode) {
            $this->fakeHALProducts->addProduct($productCode->value());
        }

        $hal = $this->fakeHALProducts->getFakeHAL();

        return new ApiResponse(
            Response::HTTP_OK,
            json_decode($hal, true)
        );
    }
}
