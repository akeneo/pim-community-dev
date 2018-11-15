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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AttributesMapping\AttributesMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping\OptionsMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\Request;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\RequestCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionsCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\ClientException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\OptionsMapping as FranklinAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\SubscriptionCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\Franklin;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\FamilyNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\SubscriptionsCursor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * TODO: There are lot of spec to add. Half of the class is not spec.
 */
class FranklinSpec extends ObjectBehavior
{
    public function let(
        AuthenticationApiInterface $authenticationApi,
        SubscriptionApiInterface $subscriptionApi,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingApiInterface $identifiersMappingApi,
        AttributesMappingApiInterface $attributesMappingApi,
        OptionsMappingInterface $attributeOptionsMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer,
        AttributesMappingNormalizer $attributesMappingNormalizer,
        FamilyNormalizer $familyNormalizer,
        ConfigurationRepositoryInterface $configurationRepository
    ): void {
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepository->find()->willReturn($configuration);
        $this->beConstructedWith(
            $authenticationApi,
            $subscriptionApi,
            $identifiersMappingRepository,
            $identifiersMappingApi,
            $attributesMappingApi,
            $attributeOptionsMappingApi,
            $identifiersMappingNormalizer,
            $attributesMappingNormalizer,
            $familyNormalizer,
            $configurationRepository
        );
    }

    public function it_is_franklin_adapter(): void
    {
        $this->shouldHaveType(Franklin::class);
        $this->shouldImplement(DataProviderInterface::class);
    }

    public function it_throws_an_exception_if_no_mapping_has_been_defined(
        ProductInterface $product,
        $identifiersMappingRepository
    ): void {
        $identifiersMappingRepository->find()->willReturn(new IdentifiersMapping([]));
        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());

        $this->shouldThrow(ProductSubscriptionException::class)->during('subscribe', [$productSubscriptionRequest]);
    }

    public function it_throws_an_exception_if_product_has_no_mapped_value(
        $identifiersMappingRepository,
        ProductInterface $product,
        AttributeInterface $ean,
        ValueInterface $eanValue
    ): void {
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
        $product->getIdentifier()->willReturn(123456);

        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());

        $this->shouldThrow(ProductSubscriptionException::invalidMappedValues())
             ->during('subscribe', [$productSubscriptionRequest]);
    }

    public function it_subscribes_product_to_franklin(
        $identifiersMappingRepository,
        $subscriptionApi,
        $familyNormalizer,
        ProductInterface $product,
        AttributeInterface $ean,
        AttributeInterface $sku,
        ValueInterface $eanValue,
        ValueInterface $skuValue,
        FamilyInterface $family
    ): void {
        $identifiersMappingRepository->find()->willReturn(
            new IdentifiersMapping(
                [
                    'upc' => $ean->getWrappedObject(),
                    'asin' => $sku->getWrappedObject(),
                ]
            )
        );

        $ean->getCode()->willReturn('ean');
        $sku->getCode()->willReturn('sku');

        $product->getId()->willReturn(42);
        $product->getFamily()->willReturn($family);
        $product->getValue('ean')->willReturn($eanValue);
        $product->getValue('sku')->willReturn($skuValue);

        $eanValue->hasData()->willReturn(true);
        $skuValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('123456789');
        $skuValue->__toString()->willReturn('987654321');

        $normalizedFamily = [
            'code' => 'tshirt',
            'label' => [
                'en_US' => 'T-shirt',
                'fr_FR' => 'T-shirt',
            ],
        ];
        $familyNormalizer->normalize($family)->willReturn($normalizedFamily);

        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());
        $product->getId()->willReturn(42);

        $request = new RequestCollection();
        $request->add(new Request(
            [
                'upc' => '123456789',
                'asin' => '987654321',
            ],
            42,
            $normalizedFamily));
        $subscriptionApi->setToken(Argument::type('string'))->shouldBeCalled();
        $subscriptionApi->subscribe($request)->willReturn(new ApiResponse(200, $this->buildFakeApiResponse()));

        $this
            ->subscribe($productSubscriptionRequest)
            ->shouldReturnAnInstanceOf(ProductSubscriptionResponse::class);
    }

    public function it_updates_the_identifiers_mapping(
        IdentifiersMappingApiInterface $identifiersMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer,
        IdentifiersMapping $mapping
    ): void {
        $normalizedMapping = ['foo' => 'bar'];

        $identifiersMappingNormalizer->normalize($mapping)->shouldBeCalled()->willReturn($normalizedMapping);
        $identifiersMappingApi->setToken(Argument::type('string'))->shouldBeCalled();
        $identifiersMappingApi->update($normalizedMapping)->shouldBeCalled();

        $this->updateIdentifiersMapping($mapping);
    }

    public function it_unsubscribes_a_subscription_id_from_franklin($subscriptionApi): void
    {
        $subscriptionApi->setToken(Argument::type('string'))->shouldBeCalled();
        $subscriptionApi->unsubscribeProduct('foo-bar')->shouldBeCalled();

        $this->unsubscribe('foo-bar')->shouldReturn(null);
    }

    public function it_throws_a_product_subscription_exception_on_client_exception($subscriptionApi): void
    {
        $subscriptionApi->setToken(Argument::type('string'))->shouldBeCalled();

        $clientException = new ClientException('exception-message');
        $subscriptionApi->unsubscribeProduct('foo-bar')->willThrow($clientException);

        $this
            ->shouldThrow(new ProductSubscriptionException('exception-message'))
            ->during(
                'unsubscribe',
                ['foo-bar']
            );
    }

    public function it_gets_attributes_mapping($attributesMappingApi): void
    {
        $response = new AttributesMapping([
            [
                'from' => [
                    'id' => 'product_weight',
                    'label' => [
                        'en_us' => 'Product Weight',
                    ],
                    'type' => 'metric',
                ],
                'to' => null,
                'summary' => ['23kg',  '12kg'],
                'status' => 'pending',
            ],
            [
                'from' => [
                    'id' => 'color',
                    'type' => 'multiselect',
                ],
                'to' => ['id' => 'color'],
                'status' => 'pending',
                'summary' => ['blue',  'red'],
            ],
        ]);
        $attributesMappingApi->fetchByFamily('camcorders')->willReturn($response);
        $attributesMappingApi->setToken(Argument::type('string'))->shouldBeCalled();

        $attributesMappingResponse = $this->getAttributesMapping('camcorders');
        $attributesMappingResponse->shouldHaveCount(2);
    }

    // Next specs are about `fetch()` method and don't need more spec.
    public function it_fetches_products_subscriptions($subscriptionApi, SubscriptionsCollection $page): void
    {
        $subscriptionApi->fetchProducts()->willReturn($page);
        $subscriptionApi->setToken(Argument::type('string'))->shouldBeCalled();

        $cursor = $this->fetch();
        $cursor->shouldBeAnInstanceOf(SubscriptionsCursor::class);
    }

    public function it_throws_product_subscription_exception_if_something_went_wrong_during_fetch(
        $subscriptionApi
    ): void {
        $clientException = new ClientException('An exception message');
        $subscriptionApi->setToken(Argument::type('string'))->shouldBeCalled();
        $subscriptionApi->fetchProducts()->willThrow($clientException);

        $this->shouldThrow(new ProductSubscriptionException('An exception message'))->during('fetch');
    }

    public function it_updates_attributes_mapping($attributesMappingApi, $attributesMappingNormalizer): void
    {
        $familyCode = 'foobar';
        $attributesMapping = ['foo' => 'bar'];
        $normalizedMapping = ['bar' => 'foo'];

        $attributesMappingNormalizer->normalize($attributesMapping)->willReturn($normalizedMapping);
        $attributesMappingApi->setToken(Argument::type('string'))->shouldBeCalled();
        $attributesMappingApi->update($familyCode, $normalizedMapping)->shouldBeCalled();

        $this->updateAttributesMapping($familyCode, $attributesMapping);
    }

    public function it_retrieves_attribute_options_mapping($attributeOptionsMappingApi): void
    {
        $filepath = realpath(FakeClient::FAKE_PATH) . '/mapping/router/attributes/color/options.json';
        $mappingData = json_decode(file_get_contents($filepath), true);

        $strFamilyCode = 'family_code';
        $strFranklinAttrId = 'franklin_attr_id';
        $attributeOptionsMappingApi->setToken(Argument::type('string'))->shouldBeCalled();
        $attributeOptionsMappingApi
            ->fetchByFamilyAndAttribute($strFamilyCode, $strFranklinAttrId)
            ->willReturn(new FranklinAttributeOptionsMapping($mappingData));

        $this
            ->getAttributeOptionsMapping(new FamilyCode($strFamilyCode), new FranklinAttributeId($strFranklinAttrId))
            ->shouldReturnAnInstanceOf(AttributeOptionsMapping::class);
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
                            'id' => 'a3fd0f30-c689-4a9e-84b4-7eac1f661923',
                            'identifiers' => [],
                            'attributes' => [],
                            'extra' => [
                                'tracker_id' => 42,
                                'family' => [
                                    'code' => 'laptop',
                                    'label' => ['en_US' => 'Laptop'],
                                ],
                            ],
                            'misses_mapping' => false,
                        ],
                    ],
                ],
            ]
        );
    }
}
