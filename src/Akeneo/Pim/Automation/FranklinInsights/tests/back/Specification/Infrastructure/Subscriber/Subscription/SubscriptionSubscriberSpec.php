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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductUnsubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\ProductSubscriptionUpdater;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Subscription\SubscriptionSubscriber;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubscriptionSubscriberSpec extends ObjectBehavior
{
    public function let(ProductSubscriptionUpdater $productSubscriptionUpdater): void
    {
        $this->beConstructedWith($productSubscriptionUpdater);
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
        $this::getSubscribedEvents()->shouldHaveKey(ProductUnsubscribed::EVENT_NAME);
    }

    public function it_reindexes_product_which_has_been_subscribed(
        ProductSubscriptionUpdater $productSubscriptionUpdater,
        ProductSubscribed $event,
        ProductSubscription $productSubscription
    ): void {
        $event->getProductSubscription()->willReturn($productSubscription);
        $productSubscription->getProductId()->willReturn(42);
        $productSubscriptionUpdater->updateSubscribedProduct(42)->shouldBeCalled();

        $this->updateSubscribedProduct($event);
    }

    public function it_reindexes_product_which_has_been_unsubscribed(
        ProductSubscriptionUpdater $productSubscriptionUpdater,
        ProductUnsubscribed $event
    ): void {
        $event->getUnsubscribedProductId()->willReturn(42);
        $productSubscriptionUpdater->updateUnsubscribedProduct(42)->shouldBeCalled();

        $this->updateUnsubscribedProduct($event);
    }
}
