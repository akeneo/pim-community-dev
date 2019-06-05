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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SubscriptionProviderSpec extends ObjectBehavior
{
    public function let(
        SubscriptionWebService $subscriptionApi,
        ConfigurationRepositoryInterface $configurationRepo,
        FamilyRepositoryInterface $familyRepository
    ): void {
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);

        $subscriptionApi->setToken('valid-token')->shouldBeCalled();

        $this->beConstructedWith($subscriptionApi, $configurationRepo, $familyRepository);
    }

    public function it_throws_an_exception_if_product_has_no_mapped_value_on_subscription(): void
    {
        $productId = new ProductId(42);
        $productSubscriptionRequest = new ProductSubscriptionRequest(
            $productId,
            new Family(new FamilyCode('a_family'), []),
            new ProductIdentifierValues($productId, []),
            'a_product'
        );

        $this->shouldThrow(ProductSubscriptionException::invalidMappedValues())
            ->during('subscribe', [$productSubscriptionRequest]);
    }

    public function it_throws_an_exception_if_data_provider_response_has_warnings(SubscriptionWebService $subscriptionApi): void
    {
        $productId = new ProductId(42);
        $productSubscriptionRequest = new ProductSubscriptionRequest(
            $productId,
            new Family(new FamilyCode('a_family'), []),
            new ProductIdentifierValues($productId, ['upc' => '123456789', 'asin' => '987654321']),
            'a_product'
        );

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

    public function it_bulk_subscribes_product_to_franklin(SubscriptionWebService $subscriptionApi): void {
        $productId = new ProductId(42);
        $productSubscriptionRequest = new ProductSubscriptionRequest(
            $productId,
            new Family(new FamilyCode('a_family'), []),
            new ProductIdentifierValues($productId, ['upc' => '123456789', 'asin' => '987654321']),
            'a_product'
        );

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

        $this->unsubscribe(new SubscriptionId('123456'));
    }

    public function it_throws_a_data_provider_exception_when_server_is_down_on_unsubscription($subscriptionApi): void
    {
        $thrownException = new FranklinServerException();
        $subscriptionApi->unsubscribeProduct('fake-subscription-id')->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::serverIsDown($thrownException))
            ->during('unsubscribe', [new SubscriptionId('fake-subscription-id')]);
    }

    public function it_throws_a_data_provider_exception_when_token_is_invalid_on_unsubscription($subscriptionApi): void
    {
        $thrownException = new InvalidTokenException();
        $subscriptionApi->unsubscribeProduct('fake-subscription-id')->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::authenticationError($thrownException))
            ->during('unsubscribe', [new SubscriptionId('fake-subscription-id')]);
    }

    public function it_throws_a_data_provider_exception_when_bad_request_occurs_on_unsubscription(
        $subscriptionApi
    ): void {
        $thrownException = new BadRequestException();
        $subscriptionApi->unsubscribeProduct('fake-subscription-id')->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::badRequestError($thrownException))
            ->during('unsubscribe', [new SubscriptionId('fake-subscription-id')]);
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

        $family = new Family(
            new FamilyCode('new_family_code'),
            ['en_US' => 'My new family label']
        );

        $this->updateFamilyInfos(new SubscriptionId('123456-987654'), $family);
    }

    public function it_throws_a_data_provider_exception_when_server_is_down_on_family_infos_update(
        $subscriptionApi
    ): void {
        $subscriptionId = '123456';
        $family = new Family(new FamilyCode('new_family_code'), []);

        $thrownException = new FranklinServerException();
        $subscriptionApi->updateFamilyInfos($subscriptionId, Argument::any())->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::serverIsDown($thrownException))
            ->during('updateFamilyInfos', [new SubscriptionId($subscriptionId), $family]);
    }

    public function it_throws_a_data_provider_exception_when_token_is_invalid_on_family_infos_update($subscriptionApi): void
    {
        $subscriptionId = '123456';
        $family = new Family(new FamilyCode('new_family_code'), []);

        $thrownException = new InvalidTokenException();
        $subscriptionApi->updateFamilyInfos($subscriptionId, Argument::any())->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::authenticationError($thrownException))
            ->during('updateFamilyInfos', [new SubscriptionId($subscriptionId), $family]);
    }

    public function it_throws_a_data_provider_exception_when_bad_request_occurs_on_family_infos_update($subscriptionApi): void
    {
        $subscriptionId = '123456';
        $family = new Family(new FamilyCode('new_family_code'), []);

        $thrownException = new BadRequestException();
        $subscriptionApi->updateFamilyInfos($subscriptionId, Argument::any())->willThrow($thrownException);

        $this
            ->shouldThrow(DataProviderException::badRequestError($thrownException))
            ->during('updateFamilyInfos', [new SubscriptionId($subscriptionId), $family]);
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
