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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Subscription;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductsSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Subscription\SubscriptionSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubscriptionSubscriberSpec extends ObjectBehavior
{
    public function let(IndexerInterface $indexer, BulkIndexerInterface $bulkIndexer): void
    {
        $this->beConstructedWith($indexer, $bulkIndexer);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_is_a_subscription_subscriber(): void
    {
        $this->shouldHaveType(SubscriptionSubscriber::class);
    }

    public function it_subscribes_to_a_product_subscribed(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(ProductSubscribed::EVENT_NAME);
        $this::getSubscribedEvents()->shouldHaveKey(ProductsSubscribed::EVENT_NAME);
    }

    public function it_reindexes_product_which_has_been_subscribed(
        $indexer,
        ProductSubscribed $event,
        ProductInterface $subscribedProduct
    ):void {
        $event->getSubscribedProduct()->willReturn($subscribedProduct);
        $indexer->index($subscribedProduct)->shouldBeCalled();

        $this->reindexProduct($event);
    }

    public function it_reindexes_products_which_have_been_subscribed(
        $bulkIndexer,
        ProductsSubscribed $event,
        ProductInterface $subscribedProductA,
        ProductInterface $subscribedProductB,
        ProductInterface $subscribedProductC
    ): void {
        $event->getSubscribedProducts()->willReturn([$subscribedProductA, $subscribedProductB, $subscribedProductC]);
        $bulkIndexer->indexAll([$subscribedProductA, $subscribedProductB, $subscribedProductC])->shouldBeCalled();

        $this->bulkReindexProducts($event);
    }
}
