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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductInfosForSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductInfosForSubscriptionQueryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetProductSubscriptionStatusHandlerSpec extends ObjectBehavior
{
    public function let(
        GetConnectionStatusHandler $getConnectionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ): void {
        $this->beConstructedWith($getConnectionStatusHandler, $selectProductInfosForSubscriptionQuery);
    }

    public function it_is_a_product_subscription_query_handler(): void
    {
        $this->shouldBeAnInstanceOf(GetProductSubscriptionStatusHandler::class);
    }

    public function it_returns_a_product_subscription_status_for_a_subscribed_product(
        GetConnectionStatusHandler $getConnectionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ): void {
        $productId = new ProductId(42);
        $query = new GetProductSubscriptionStatusQuery($productId);

        $selectProductInfosForSubscriptionQuery->execute($productId)->willReturn(new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, ['mpn' => '123456']),
            new Family(new FamilyCode('a_family'), []),
            'a_product',
            false,
            true
        ));

        $connectionStatus = new ConnectionStatus(true, false, true, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateAnActiveSubscription();
    }

    public function it_returns_a_product_subscription_status_for_a_not_subscribed_product(
        GetConnectionStatusHandler $getConnectionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ): void {
        $productId = new ProductId(42);
        $query = new GetProductSubscriptionStatusQuery($productId);

        $selectProductInfosForSubscriptionQuery->execute($productId)->willReturn(new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, ['mpn' => '123456']),
            new Family(new FamilyCode('a_family'), []),
            'a_product',
            false,
            false
        ));

        $connectionStatus = new ConnectionStatus(true, false, true, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateAnInactiveSubscription();
    }

    public function it_returns_a_product_subscription_status_for_a_product_without_family(
        GetConnectionStatusHandler $getConnectionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ): void {
        $productId = new ProductId(42);
        $query = new GetProductSubscriptionStatusQuery($productId);

        $selectProductInfosForSubscriptionQuery->execute($productId)->willReturn(new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, ['mpn' => '123456']),
            null,
            'a_product',
            false,
            false
        ));

        $connectionStatus = new ConnectionStatus(true, false, true, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateThatProductDoesNotHaveFamily();
    }

    public function it_returns_a_product_subscription_status_for_a_product_with_family(
        GetConnectionStatusHandler $getConnectionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ): void {
        $productId = new ProductId(42);
        $query = new GetProductSubscriptionStatusQuery($productId);

        $selectProductInfosForSubscriptionQuery->execute($productId)->willReturn(new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, ['mpn' => '123456']),
            new Family(new FamilyCode('a_family'), []),
            'a_product',
            false,
            false
        ));

        $connectionStatus = new ConnectionStatus(true, false, true, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);


        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateThatProductHasFamily();
    }

    public function it_returns_a_product_subscription_status_for_a_product_with_identifiers_mapping_filled(
        GetConnectionStatusHandler $getConnectionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ): void {
        $productId = new ProductId(42);
        $query = new GetProductSubscriptionStatusQuery($productId);

        $selectProductInfosForSubscriptionQuery->execute($productId)->willReturn(new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, ['mpn' => '123456']),
            new Family(new FamilyCode('a_family'), []),
            'a_product',
            false,
            false
        ));

        $connectionStatus = new ConnectionStatus(true, false, true, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateThatProductFillsIdentifiersMapping();
    }

    public function it_returns_a_product_subscription_status_for_a_product_with_identifiers_mapping_not_filled(
        GetConnectionStatusHandler $getConnectionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ): void {
        $productId = new ProductId(42);
        $query = new GetProductSubscriptionStatusQuery($productId);

        $selectProductInfosForSubscriptionQuery->execute($productId)->willReturn(new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, []),
            new Family(new FamilyCode('a_family'), []),
            'a_product',
            false,
            false
        ));

        $connectionStatus = new ConnectionStatus(true, false, true, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateThatProductDoesNotFillIdentifiersMapping();
    }

    public function it_returns_product_subscription_status_with_a_connection_status(
        GetConnectionStatusHandler $getConnectionStatusHandler,
        SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ): void {
        $productId = new ProductId(42);
        $query = new GetProductSubscriptionStatusQuery($productId);

        $selectProductInfosForSubscriptionQuery->execute($productId)->willReturn(new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, ['mpn' => '123456']),
            new Family(new FamilyCode('a_family'), []),
            'a_product',
            false,
            false
        ));

        $connectionStatus = new ConnectionStatus(true, false, true, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldHaveAConnectionStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchers(): array
    {
        return [
            'indicateAnActiveSubscription' => function (ProductSubscriptionStatus $productSubscriptionStatus) {
                return $productSubscriptionStatus->isSubscribed();
            },
            'indicateAnInactiveSubscription' => function (ProductSubscriptionStatus $productSubscriptionStatus) {
                return !$productSubscriptionStatus->isSubscribed();
            },
            'haveAConnectionStatus' => function (ProductSubscriptionStatus $productSubscriptionStatus) {
                return $productSubscriptionStatus->getConnectionStatus() instanceof ConnectionStatus;
            },
            'indicateThatProductHasFamily' => function (ProductSubscriptionStatus $productSubscriptionStatus) {
                return $productSubscriptionStatus->hasFamily();
            },
            'indicateThatProductDoesNotHaveFamily' => function (ProductSubscriptionStatus $productSubscriptionStatus) {
                return !$productSubscriptionStatus->hasFamily();
            },
            'indicateThatProductFillsIdentifiersMapping' => function (
                ProductSubscriptionStatus $productSubscriptionStatus
            ) {
                return $productSubscriptionStatus->isMappingFilled();
            },
            'indicateThatProductDoesNotFillIdentifiersMapping' => function (
                ProductSubscriptionStatus $productSubscriptionStatus
            ) {
                return !$productSubscriptionStatus->isMappingFilled();
            },
        ];
    }
}
