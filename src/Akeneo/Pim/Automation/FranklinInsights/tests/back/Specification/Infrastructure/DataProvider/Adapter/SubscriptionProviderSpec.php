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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\ApiResponse;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\Request;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\RequestCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionsCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\SubscriptionCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\WarningCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\SubscriptionsCursor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslation;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SubscriptionProviderSpec extends ObjectBehavior
{
    public function let(
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        SubscriptionWebService $subscriptionApi,
        ConfigurationRepositoryInterface $configurationRepo
    ): void {
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);

        $subscriptionApi->setToken('valid-token')->shouldBeCalled();

        $this->beConstructedWith($identifiersMappingRepository, $subscriptionApi, $configurationRepo);
    }

    public function it_throws_an_exception_if_no_mapping_has_been_defined_for_subscription(
        ProductInterface $product,
        $identifiersMappingRepository
    ): void {
        $identifiersMappingRepository->find()->willReturn(new IdentifiersMapping([]));
        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());

        $this->shouldThrow(ProductSubscriptionException::class)->during('subscribe', [$productSubscriptionRequest]);
    }

    public function it_throws_an_exception_if_product_has_no_mapped_value_on_subscription(
        $identifiersMappingRepository,
        ProductInterface $product,
        AttributeInterface $ean,
        ValueInterface $eanValue
    ): void {
        $identifiersMapping = new IdentifiersMapping(['upc' => $ean->getWrappedObject()]);
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);

        $ean->getCode()->willReturn('ean');
        $eanValue->hasData()->willReturn(false);
        $product->getValue('ean')->willReturn($eanValue);
        $product->getId()->willReturn(42);
        $product->getIdentifier()->willReturn(123456);

        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());

        $this->shouldThrow(ProductSubscriptionException::invalidMappedValues())
            ->during('subscribe', [$productSubscriptionRequest]);
    }

    public function it_throws_an_exception_if_data_provider_response_has_warnings(
        $identifiersMappingRepository,
        $subscriptionApi,
        ProductInterface $product,
        AttributeInterface $ean,
        AttributeInterface $sku,
        ValueInterface $eanValue,
        ValueInterface $skuValue,
        FamilyInterface $family
    ): void {
        $identifiersMapping = new IdentifiersMapping(
            [
                'upc' => $ean->getWrappedObject(),
                'asin' => $sku->getWrappedObject(),
            ]
        );
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);

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

        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());
        $product->getId()->willReturn(42);

        $family->getTranslations()->willReturn([]);
        $family->getCode()->willReturn('a_family');

        $request = new RequestCollection();
        $request->add(new Request(
            [
                'upc' => '123456789',
                'asin' => '987654321',
            ],
            42,
            [
                'code' => 'a_family',
                'label' => [],
            ]
        ));
        $subscriptionApi->subscribe($request)->willReturn(
            new ApiResponse(
                new SubscriptionCollection($this->fakeApiResponse()),
                new WarningCollection($this->fakeApiResponse(true))
            )
        );

        $this->shouldThrow(ProductSubscriptionException::invalidMappedValues())
            ->during('subscribe', [$productSubscriptionRequest]);
    }

    public function it_bulk_subscribes_product_to_franklin(
        $identifiersMappingRepository,
        $subscriptionApi,
        ProductInterface $product,
        AttributeInterface $ean,
        AttributeInterface $sku,
        ValueInterface $eanValue,
        ValueInterface $skuValue,
        FamilyInterface $family
    ): void {
        $identifiersMapping = new IdentifiersMapping(
            [
                'upc' => $ean->getWrappedObject(),
                'asin' => $sku->getWrappedObject(),
            ]
        );
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);

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

        $productSubscriptionRequest = new ProductSubscriptionRequest($product->getWrappedObject());
        $product->getId()->willReturn(42);

        $family->getTranslations()->willReturn([]);
        $family->getCode()->willReturn('a_family');

        $request = new RequestCollection();
        $request->add(new Request(
            [
                'upc' => '123456789',
                'asin' => '987654321',
            ],
            42,
            [
                'code' => 'a_family',
                'label' => [],
            ]
        ));
        $subscriptionApi->subscribe($request)->willReturn(
            new ApiResponse(
                new SubscriptionCollection($this->fakeApiResponse()),
                new WarningCollection($this->fakeApiResponse())
            )
        );

        $this
            ->subscribe($productSubscriptionRequest)
            ->shouldReturnAnInstanceOf(ProductSubscriptionResponse::class);
    }

    public function it_fetches_products_subscriptions($subscriptionApi, SubscriptionsCollection $page): void
    {
        $datetime = new \DateTime('2012-01-01');
        $subscriptionApi->fetchProducts(null, $datetime)->willReturn($page);

        $this->fetch($datetime)->shouldBeAnInstanceOf(SubscriptionsCursor::class);
    }

    public function it_throws_a_data_provider_exception_when_server_is_down_on_fetch($subscriptionApi): void
    {
        $datetime = new \DateTime('2012-01-01');
        $thrownException = new FranklinServerException();
        $subscriptionApi->fetchProducts(null, $datetime)->willThrow($thrownException);

        $this->shouldThrow(DataProviderException::serverIsDown($thrownException))->during('fetch', [$datetime]);
    }

    public function it_throws_a_data_provider_exception_when_token_is_invalid_on_fetch($subscriptionApi): void
    {
        $datetime = new \DateTime('2012-01-01');
        $thrownException = new InvalidTokenException();
        $subscriptionApi->fetchProducts(null, $datetime)->willThrow($thrownException);

        $this->shouldThrow(DataProviderException::authenticationError())->during('fetch', [$datetime]);
    }

    public function it_unsubscribes_a_subscription($subscriptionApi): void
    {
        $subscriptionApi->unsubscribeProduct('123456')->shouldBeCalled();

        $this->unsubscribe('123456');
    }

    public function it_throws_a_data_provider_exception_when_server_is_down_on_unsubscription($subscriptionApi): void
    {
        $thrownException = new FranklinServerException();
        $subscriptionApi->unsubscribeProduct(Argument::any())->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::serverIsDown($thrownException))
            ->during('unsubscribe', [Argument::any()]);
    }

    public function it_throws_a_data_provider_exception_when_token_is_invalid_on_unsubscription($subscriptionApi): void
    {
        $thrownException = new InvalidTokenException();
        $subscriptionApi->unsubscribeProduct(Argument::any())->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::authenticationError($thrownException))
            ->during('unsubscribe', [Argument::any()]);
    }

    public function it_throws_a_data_provider_exception_when_bad_request_occurs_on_unsubscription(
        $subscriptionApi
    ): void {
        $thrownException = new BadRequestException();
        $subscriptionApi->unsubscribeProduct(Argument::any())->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::badRequestError($thrownException))
            ->during('unsubscribe', [Argument::any()]);
    }

    public function it_updates_family_infos_for_a_subscription($subscriptionApi): void
    {
        $subscriptionApi->updateFamilyInfos(
            '123456-987654',
            [
                'code' => 'new_family_code',
                'label' => [
                    'en_US' => 'My new family label',
                ],
            ]
        )->shouldBeCalled();

        $translation = new FamilyTranslation();
        $translation->setLocale('en_US');
        $translation->setLabel('My new family label');
        $family = new Family();
        $family->setCode('new_family_code');
        $family->addTranslation($translation);

        $this->updateFamilyInfos('123456-987654', $family);
    }

    public function it_throws_a_data_provider_exception_when_server_is_down_on_family_infos_update(
        $subscriptionApi,
        FamilyInterface $family
    ): void {
        $family->getCode()->willReturn('foo');
        $family->getTranslations()->willReturn([]);

        $subscriptionId = '123456';
        $familyInfos = Argument::any();

        $thrownException = new FranklinServerException();
        $subscriptionApi->updateFamilyInfos($subscriptionId, $familyInfos)->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::serverIsDown($thrownException))
            ->during('updateFamilyInfos', [$subscriptionId, $family]);
    }

    public function it_throws_a_data_provider_exception_when_token_is_invalid_on_family_infos_update(
        $subscriptionApi,
        FamilyInterface $family
    ): void {
        $family->getCode()->willReturn('foo');
        $family->getTranslations()->willReturn([]);

        $subscriptionId = '123456';
        $familyInfos = Argument::any();

        $thrownException = new InvalidTokenException();
        $subscriptionApi->updateFamilyInfos($subscriptionId, $familyInfos)->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::authenticationError($thrownException))
            ->during('updateFamilyInfos', [$subscriptionId, $family]);
    }

    public function it_throws_a_data_provider_exception_when_bad_request_occurs_on_family_infos_update(
        $subscriptionApi,
        FamilyInterface $family
    ): void {
        $family->getCode()->willReturn('foo');
        $family->getTranslations()->willReturn([]);

        $subscriptionId = '123456';
        $familyInfos = Argument::any();

        $thrownException = new BadRequestException();
        $subscriptionApi->updateFamilyInfos($subscriptionId, $familyInfos)->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::badRequestError($thrownException))
            ->during('updateFamilyInfos', [$subscriptionId, $family]);
    }

    /**
     * @param bool $withErrors
     *
     * @return array
     */
    private function fakeApiResponse(bool $withErrors = false): array
    {
        $errors = [];
        if (true === $withErrors) {
            $errors = [
                [
                    'message' => 'warning message 1',
                    'entry' => [
                        'tracker_id' => '44',
                    ],
                ],
            ];
        }

        return [
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
                        'mapped_identifiers' => [],
                        'mapped_attributes' => [],
                        'misses_mapping' => false,
                    ],
                ],
            ],
            'warnings' => $errors,
        ];
    }
}
