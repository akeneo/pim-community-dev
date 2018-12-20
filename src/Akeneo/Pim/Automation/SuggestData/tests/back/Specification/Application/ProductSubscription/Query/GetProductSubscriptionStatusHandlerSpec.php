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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifierMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetProductSubscriptionStatusHandlerSpec extends ObjectBehavior
{
    public function let(
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        GetConnectionStatusHandler $getConnectionStatusHandler,
        ProductRepositoryInterface $productRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ): void {
        $this->beConstructedWith(
            $productSubscriptionRepository,
            $getConnectionStatusHandler,
            $productRepository,
            $identifiersMappingRepository
        );
    }

    public function it_is_a_product_subscription_query_handler(): void
    {
        $this->shouldBeAnInstanceOf(GetProductSubscriptionStatusHandler::class);
    }

    public function it_returns_a_product_subscription_status_for_a_subscribed_product(
        $productSubscriptionRepository,
        $getConnectionStatusHandler,
        $productRepository,
        $identifiersMappingRepository,
        AttributeInterface $mpn,
        AttributeInterface $asin,
        ProductSubscription $productSubscription,
        ProductInterface $product,
        IdentifiersMapping $identifiersMapping
    ): void {
        $query = new GetProductSubscriptionStatusQuery(42);

        $productSubscriptionRepository->findOneByProductId(42)->willReturn($productSubscription);

        $connectionStatus = new ConnectionStatus(false, false, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productRepository->find(42)->willReturn($product);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->getIdentifiers()->willReturn([
            'brand' => new IdentifierMapping('brand', null),
            'ean' => new IdentifierMapping('ean', null),
            'mpn' => new IdentifierMapping('mpn', $mpn->getWrappedObject()),
            'asin' => new IdentifierMapping('asin', $asin->getWrappedObject()),
        ]);
        $mpn->getCode()->willReturn('pim_mpn');
        $asin->getCode()->willReturn('pim_asin');
        $product->getFamily()->willReturn(null);
        $product->isVariant()->willReturn(false);
        $product->getValue('pim_mpn')->willReturn(null);
        $product->getValue('pim_asin')->willReturn(null);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateAnActiveSubscription();
    }

    public function it_returns_a_product_subscription_status_for_a_not_subscribed_product(
        $productSubscriptionRepository,
        $getConnectionStatusHandler,
        $productRepository,
        $identifiersMappingRepository,
        AttributeInterface $mpn,
        AttributeInterface $asin,
        ProductInterface $product,
        IdentifiersMapping $identifiersMapping
    ): void {
        $query = new GetProductSubscriptionStatusQuery(42);

        $productSubscriptionRepository->findOneByProductId(42)->willReturn(null);

        $connectionStatus = new ConnectionStatus(false, false, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productRepository->find(42)->willReturn($product);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->getIdentifiers()->willReturn([
            'brand' => new IdentifierMapping('brand', null),
            'ean' => new IdentifierMapping('ean', null),
            'mpn' => new IdentifierMapping('mpn', $mpn->getWrappedObject()),
            'asin' => new IdentifierMapping('asin', $asin->getWrappedObject()),
        ]);
        $mpn->getCode()->willReturn('pim_mpn');
        $asin->getCode()->willReturn('pim_asin');
        $product->getFamily()->willReturn(null);
        $product->isVariant()->willReturn(false);
        $product->getValue('pim_mpn')->willReturn(null);
        $product->getValue('pim_asin')->willReturn(null);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateAnInactiveSubscription();
    }

    public function it_returns_a_product_subscription_status_for_a_product_without_family(
        $productSubscriptionRepository,
        $getConnectionStatusHandler,
        $productRepository,
        $identifiersMappingRepository,
        AttributeInterface $mpn,
        AttributeInterface $asin,
        ProductInterface $product,
        IdentifiersMapping $identifiersMapping
    ): void {
        $query = new GetProductSubscriptionStatusQuery(42);

        $productSubscriptionRepository->findOneByProductId(42)->willReturn(null);

        $connectionStatus = new ConnectionStatus(false, false, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productRepository->find(42)->willReturn($product);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->getIdentifiers()->willReturn([
            'brand' => new IdentifierMapping('brand', null),
            'ean' => new IdentifierMapping('ean', null),
            'mpn' => new IdentifierMapping('mpn', $mpn->getWrappedObject()),
            'asin' => new IdentifierMapping('asin', $asin->getWrappedObject()),
        ]);
        $mpn->getCode()->willReturn('pim_mpn');
        $asin->getCode()->willReturn('pim_asin');
        $product->getFamily()->willReturn(null);
        $product->isVariant()->willReturn(false);
        $product->getValue('pim_mpn')->willReturn(null);
        $product->getValue('pim_asin')->willReturn(null);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateThatProductDoesNotHaveFamily();
    }

    public function it_returns_a_product_subscription_status_for_a_product_with_family(
        $productSubscriptionRepository,
        $getConnectionStatusHandler,
        $productRepository,
        $identifiersMappingRepository,
        AttributeInterface $mpn,
        AttributeInterface $asin,
        ProductInterface $product,
        FamilyInterface $family,
        IdentifiersMapping $identifiersMapping
    ): void {
        $query = new GetProductSubscriptionStatusQuery(42);

        $productSubscriptionRepository->findOneByProductId(42)->willReturn(null);

        $connectionStatus = new ConnectionStatus(false, false, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productRepository->find(42)->willReturn($product);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->getIdentifiers()->willReturn([
            'brand' => new IdentifierMapping('brand', null),
            'ean' => new IdentifierMapping('ean', null),
            'mpn' => new IdentifierMapping('mpn', $mpn->getWrappedObject()),
            'asin' => new IdentifierMapping('asin', $asin->getWrappedObject()),
        ]);
        $mpn->getCode()->willReturn('pim_mpn');
        $asin->getCode()->willReturn('pim_asin');
        $product->getFamily()->willReturn($family);
        $product->isVariant()->willReturn(false);
        $product->getValue('pim_mpn')->willReturn(null);
        $product->getValue('pim_asin')->willReturn(null);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateThatProductHasFamily();
    }

    public function it_returns_a_product_subscription_status_for_a_product_with_identifiers_mapping_filled(
        $productSubscriptionRepository,
        $getConnectionStatusHandler,
        $productRepository,
        $identifiersMappingRepository,
        AttributeInterface $mpn,
        AttributeInterface $asin,
        AttributeInterface $brand,
        AttributeInterface $ean,
        ProductInterface $product,
        ValueInterface $mpnValue,
        ValueInterface $eanValue,
        IdentifiersMapping $identifiersMapping
    ): void {
        $query = new GetProductSubscriptionStatusQuery(42);

        $productSubscriptionRepository->findOneByProductId(42)->willReturn(null);

        $connectionStatus = new ConnectionStatus(false, false, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productRepository->find(42)->willReturn($product);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->getIdentifiers()->willReturn([
            'brand' => new IdentifierMapping('brand', $brand->getWrappedObject()),
            'ean' => new IdentifierMapping('ean', $ean->getWrappedObject()),
            'mpn' => new IdentifierMapping('mpn', $mpn->getWrappedObject()),
            'asin' => new IdentifierMapping('asin', $asin->getWrappedObject()),
        ]);
        $mpn->getCode()->willReturn('pim_mpn');
        $asin->getCode()->willReturn('pim_asin');
        $brand->getCode()->willReturn('pim_brand');
        $ean->getCode()->willReturn('pim_ean');
        $product->getFamily()->willReturn(null);
        $product->isVariant()->willReturn(false);
        $product->getValue('pim_brand')->willReturn(null);
        $product->getValue('pim_mpn')->willReturn($mpnValue);
        $product->getValue('pim_ean')->willReturn($eanValue);
        $product->getValue('pim_asin')->willReturn(null);

        $mpnValue->getData()->willReturn(null);
        $eanValue->getData()->willReturn(12345);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateThatProductFillsIdentifiersMapping();
    }

    public function it_returns_a_product_subscription_status_for_a_product_with_identifiers_mapping_not_filled(
        $productSubscriptionRepository,
        $getConnectionStatusHandler,
        $productRepository,
        $identifiersMappingRepository,
        AttributeInterface $mpn,
        AttributeInterface $brand,
        AttributeInterface $ean,
        ProductInterface $product,
        ValueInterface $mpnValue,
        ValueInterface $eanValue,
        IdentifiersMapping $identifiersMapping
    ): void {
        $query = new GetProductSubscriptionStatusQuery(42);

        $productSubscriptionRepository->findOneByProductId(42)->willReturn(null);

        $connectionStatus = new ConnectionStatus(false, false, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productRepository->find(42)->willReturn($product);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->getIdentifiers()->willReturn([
            'brand' => new IdentifierMapping('brand', $brand->getWrappedObject()),
            'ean' => new IdentifierMapping('ean', $ean->getWrappedObject()),
            'mpn' => new IdentifierMapping('mpn', $mpn->getWrappedObject()),
            'asin' => new IdentifierMapping('mpn', null),
        ]);
        $brand->getCode()->willReturn('pim_brand');
        $mpn->getCode()->willReturn('pim_mpn');
        $ean->getCode()->willReturn('pim_ean');
        $product->getFamily()->willReturn(null);
        $product->isVariant()->willReturn(false);
        $product->getValue('pim_brand')->willReturn(null);
        $product->getValue('pim_mpn')->willReturn($mpnValue);
        $product->getValue('pim_ean')->willReturn($eanValue);

        $mpnValue->getData()->willReturn(null);
        $eanValue->getData()->willReturn(null);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateThatProductDoesNotFillIdentifiersMapping();
    }

    public function it_returns_product_subscription_status_with_a_connection_status(
        $productSubscriptionRepository,
        $getConnectionStatusHandler,
        $productRepository,
        $identifiersMappingRepository,
        AttributeInterface $mpn,
        AttributeInterface $asin,
        ProductSubscription $productSubscription,
        ProductInterface $product,
        IdentifiersMapping $identifiersMapping
    ): void {
        $query = new GetProductSubscriptionStatusQuery(42);

        $productSubscriptionRepository->findOneByProductId(42)->willReturn($productSubscription);

        $connectionStatus = new ConnectionStatus(true, true, 0);
        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $productRepository->find(42)->willReturn($product);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->getIdentifiers()->willReturn([
            'brand' => new IdentifierMapping('brand', null),
            'ean' => new IdentifierMapping('ean', null),
            'mpn' => new IdentifierMapping('mpn', $mpn->getWrappedObject()),
            'asin' => new IdentifierMapping('asin', $asin->getWrappedObject()),
        ]);
        $mpn->getCode()->willReturn('pim_mpn');
        $asin->getCode()->willReturn('pim_asin');
        $product->getFamily()->willReturn(null);
        $product->isVariant()->willReturn(false);
        $product->getValue('pim_mpn')->willReturn(null);
        $product->getValue('pim_asin')->willReturn(null);

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
