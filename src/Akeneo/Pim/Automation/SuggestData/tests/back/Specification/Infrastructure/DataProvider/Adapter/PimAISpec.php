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

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Exception\ClientException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\PimAI;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslation;
use PhpSpec\ObjectBehavior;

class PimAISpec extends ObjectBehavior
{
    public function let(
        AuthenticationApiInterface $authenticationApi,
        SubscriptionApiInterface $subscriptionApi,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingApiInterface $identifiersMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer
    ) {
        $this->beConstructedWith(
            $authenticationApi,
            $subscriptionApi,
            $identifiersMappingRepository,
            $identifiersMappingApi,
            $identifiersMappingNormalizer
        );
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

        $this->shouldThrow(ProductSubscriptionException::class)->during('subscribe', [$productSubscriptionRequest]);
    }

    public function it_throws_an_exception_if_product_has_no_mapped_value(
        $identifiersMappingRepository,
        $subscriptionApi,
        ProductInterface $product,
        AttributeInterface $ean,
        ValueInterface $eanValue
    ) {
        $identifiersMappingRepository->find()->willReturn(
            new IdentifiersMapping(
                [
                    'upc' => $ean->getWrappedObject(),
                ]
            )
        );

        $ean->getCode()->willReturn('ean');
        $eanValue->hasData()->willReturn(false);
        $product->getValue('ean')->willReturn($eanValue);
        $product->getId()->willReturn(42);

        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());

        $this->shouldThrow(new ProductSubscriptionException('No mapped values for product with id "42"'))
             ->during('subscribe', [$productSubscriptionRequest]);
    }

    public function it_catches_client_exceptions(
        $identifiersMappingRepository,
        $subscriptionApi,
        ProductInterface $product,
        AttributeInterface $ean,
        ValueInterface $eanValue,
        FamilyInterface $family,
        FamilyTranslation $familyTranslation
    ) {
        $identifiersMappingRepository->find()->willReturn(
            new IdentifiersMapping(
                [
                    'upc' => $ean->getWrappedObject(),
                ]
            )
        );

        $ean->getCode()->willReturn('ean');

        $family->getCode()->willReturn('tshirt');
        $family->getLabel()->willReturn('T-shirt');
        $family->getTranslation()->willReturn($familyTranslation);
        $familyTranslation->getLocale()->willReturn('en_US');

        $product->getId()->willReturn(42);
        $product->getFamily()->willReturn($family);
        $product->getValue('ean')->willReturn($eanValue);
        $eanValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('123456789');

        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());

        $subscriptionApi->subscribeProduct(
            ['upc' => '123456789'],
            42,
            ['code' => 'tshirt', 'label' => ['en_US' => 'T-shirt']]
        )->willThrow(new ClientException('exception-message'));

        $this->shouldThrow(new ProductSubscriptionException('exception-message'))->during(
            'subscribe',
            [$productSubscriptionRequest]
        );
    }

    public function it_subscribes_product_to_pim_ai(
        $identifiersMappingRepository,
        $subscriptionApi,
        ProductInterface $product,
        AttributeInterface $ean,
        AttributeInterface $sku,
        ValueInterface $eanValue,
        ValueInterface $skuValue,
        FamilyInterface $family,
        FamilyTranslation $familyTranslation
    ) {
        $identifiersMappingRepository->find()->willReturn(
            new IdentifiersMapping(
                [
                    'upc'  => $ean->getWrappedObject(),
                    'asin' => $sku->getWrappedObject(),
                ]
            )
        );

        $ean->getCode()->willReturn('ean');
        $sku->getCode()->willReturn('sku');

        $family->getCode()->willReturn('tshirt');
        $family->getLabel()->willReturn('T-shirt');
        $family->getTranslation()->willReturn($familyTranslation);
        $familyTranslation->getLocale()->willReturn('en_US');

        $product->getId()->willReturn(42);
        $product->getFamily()->willReturn($family);
        $product->getValue('ean')->willReturn($eanValue);
        $product->getValue('sku')->willReturn($skuValue);

        $eanValue->hasData()->willReturn(true);
        $skuValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('123456789');
        $skuValue->__toString()->willReturn('987654321');

        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());
        $product->getId()->willReturn(42);

        $subscriptionApi->subscribeProduct(
            [
                'upc'  => '123456789',
                'asin' => '987654321',
            ],
            42,
            ['code' => 'tshirt', 'label' => ['en_US' => 'T-shirt']]
        )->willReturn(new ApiResponse(200, $this->buildFakeApiResponse()));

        $this
            ->subscribe($productSubscriptionRequest)
            ->shouldReturnAnInstanceOf(ProductSubscriptionResponse::class);
    }

    public function it_fetches_products_from_pim_ai($subscriptionApi)
    {
        $subscriptionApi
            ->fetchProducts()
            ->willReturn(new ApiResponse(200, $this->buildFakeApiResponse()));
    }

    public function it_updates_the_identifiers_mapping(
        IdentifiersMappingApiInterface $identifiersMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer,
        IdentifiersMapping $mapping
    ) {
        $normalizedMapping = ['foo' => 'bar'];

        $identifiersMappingNormalizer->normalize($mapping)->shouldBeCalled()->willReturn($normalizedMapping);
        $identifiersMappingApi->update($normalizedMapping)->shouldBeCalled();

        $this->updateIdentifiersMapping($mapping);
    }

    /**
     * @return SubscriptionCollection
     */
    private function buildFakeApiResponse(): SubscriptionCollection
    {
        return new SubscriptionCollection(
            [
                '_embedded' => [
                    'subscription' => [
                        0 => [
                            'id'          => 'a3fd0f30-c689-4a9e-84b4-7eac1f661923',
                            'identifiers' => [],
                            'attributes'  => [],
                            'tracker_id' => 42
                        ],
                    ],
                ],
            ]
        );
    }
}
