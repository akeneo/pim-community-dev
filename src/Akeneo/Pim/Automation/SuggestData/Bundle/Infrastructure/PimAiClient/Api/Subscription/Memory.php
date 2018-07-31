<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\Fake\FakeHALProducts;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\ValueObject\ProductCode;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\ValueObject\ProductCodeCollection;
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
