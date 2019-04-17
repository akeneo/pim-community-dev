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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Product\ProductToVariantProductSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ProductToVariantProductSubscriberSpec extends ObjectBehavior
{
    public function let(
        UnsubscribeProductHandler $unsubscribeProductHandler,
        GetConnectionStatusHandler $connectionStatusHandler
    ): void {
        $this->beConstructedWith($unsubscribeProductHandler, $connectionStatusHandler);

        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
    }

    public function it_is_a_product_family_removal_subscriber(): void
    {
        $this->shouldHaveType(ProductToVariantProductSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_post_save_events(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
    }

    public function it_does_nothing_if_the_subject_is_not_a_product(
        GenericEvent $event,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ): void {
        $event->getSubject()->willReturn(new \stdClass());
        $unsubscribeProductHandler->handle()->shouldNotBeCalled();

        $this->onPostSave($event);
    }

    public function it_does_nothing_if_the_product_is_not_variant(
        GenericEvent $event,
        ProductInterface $product,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(false);
        $unsubscribeProductHandler->handle()->shouldNotBeCalled();

        $this->onPostSave($event);
    }

    public function it_does_nothing_franklin_insights_is_not_activated(
        GenericEvent $event,
        ProductInterface $product,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        $connectionStatusHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(true);

        $connectionStatus = new ConnectionStatus(false, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);

        $unsubscribeProductHandler->handle()->shouldNotBeCalled();

        $this->onPostSave($event);
    }

    public function it_unsubscribes_a_variant_product(
        GenericEvent $event,
        ProductInterface $product,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $product->isVariant()->willReturn(true);

        $unsubscribeProductHandler->handle(new UnsubscribeProductCommand(new ProductId(1)))->shouldBeCalled();

        $this->onPostSave($event);
    }
}
