<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\PimAI;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Exceptions\MappingNotDefinedException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class PimAISpec extends ObjectBehavior
{
    public function let(AuthenticationApiInterface $authenticationApi, SubscriptionApiInterface $subscriptionApi, IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->beConstructedWith($authenticationApi, $subscriptionApi, $identifiersMappingRepository);
    }

    public function it_is_pim_ai_adapter()
    {
        $this->shouldHaveType(PimAI::class);
    }

    public function it_throws_an_exception_if_no_mapping_has_been_defined(
        ProductInterface $product,
        $identifiersMappingRepository
    ) {
        $identifiersMappingRepository->find()->willReturn(new IdentifiersMapping([]));
        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());

        $this->shouldThrow(MappingNotDefinedException::class)->during('subscribe', [$productSubscriptionRequest]);
    }

    public function it_subscribes_products_to_pim_ai(
        ProductInterface $product,
        $identifiersMappingRepository,
        $subscriptionApi,
        AttributeInterface $ean,
        AttributeInterface $sku,
        ValueInterface $eanValue,
        ValueInterface $skuValue
    )
    {
        $identifiersMappingRepository->find()->willReturn(new IdentifiersMapping([
            'upc' => $ean->getWrappedObject(),
            'asin' => $sku->getWrappedObject(),
        ]));

        $ean->getCode()->willReturn('ean');
        $sku->getCode()->willReturn('sku');

        $eanValue->hasData()->willReturn(true);
        $skuValue->hasData()->willReturn(true);

        $eanValue->__toString()->willReturn('123456789');
        $skuValue->__toString()->willReturn('987654321');

        $product->getValue('ean')->willReturn($eanValue);
        $product->getValue('sku')->willReturn($skuValue);

        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());
        $product->getId()->willReturn(42);

        $subscriptionApi->subscribeProduct([
            'upc' => '123456789',
            'asin' => '987654321',
        ])->willReturn(new ApiResponse(200, $this->buildFakeApiResponse()));

        $this
             ->subscribe($productSubscriptionRequest)
            ->shouldReturnAnInstanceOf(ProductSubscriptionResponse::class);
    }

    private function buildFakeApiResponse()
    {
        return new SubscriptionCollection([
            '_embedded' => [
                'subscription' => [
                    0 => [
                        'id' => 'a3fd0f30-c689-4a9e-84b4-7eac1f661923',
                        'identifiers' => [],
                        'attributes' => [],
                    ],
                ]
            ],
        ]);
    }
}
