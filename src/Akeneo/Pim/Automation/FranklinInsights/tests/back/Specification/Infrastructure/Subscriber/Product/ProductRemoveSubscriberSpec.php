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

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductRemoveSubscriberSpec extends ObjectBehavior
{
    public function let(UnsubscribeProductHandler $unsubscribeProductHandler): void
    {
        $this->beConstructedWith($unsubscribeProductHandler);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_on_a_post_remove_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_REMOVE => 'onPostRemove',
        ]);
    }

    public function it_unsubscribes_a_removed_product(
        $unsubscribeProductHandler
    ): void {
        $event = new RemoveEvent(
            (new Product())->setId(42),
            42
        );

        $this->onPostRemove($event);

        $unsubscribeProductHandler->handle(new UnsubscribeProductCommand(42))->shouldHaveBeenCalled();
    }

    public function it_does_nothing_if_removed_object_is_not_a_product(
        $unsubscribeProductHandler
    ): void {
        $event = new RemoveEvent(
            (new Attribute())->setId(42),
            42
        );

        $this->onPostRemove($event);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotHaveBeenCalled();
    }
}
