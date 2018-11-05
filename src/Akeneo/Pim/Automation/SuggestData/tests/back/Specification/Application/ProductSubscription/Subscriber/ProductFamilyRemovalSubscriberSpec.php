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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Subscriber\ProductFamilyRemovalSubscriber;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\Product\SelectProductFamilyIdQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductFamilyRemovalSubscriberSpec extends ObjectBehavior
{
    public function let(
        SelectProductFamilyIdQuery $selectProductFamilyIdQuery,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ): void {
        $this->beConstructedWith($selectProductFamilyIdQuery, $unsubscribeProductHandler);
    }

    public function it_is_a_product_family_removal_subscriber(): void
    {
        $this->shouldHaveType(ProductFamilyRemovalSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_pre_save_event(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_SAVE);
    }

    public function it_is_only_applied_when_event_is_dispatched_on_something_else_than_a_product(
        GenericEvent $event,
        FamilyInterface $family,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ): void {
        $event->getSubject()->willReturn($family);
        $unsubscribeProductHandler->handle()->shouldNotBeCalled();

        $this->onPreSave($event);
    }

    public function it_is_not_applied_on_product_creation(
        GenericEvent $event,
        ProductInterface $product,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(null);

        $unsubscribeProductHandler
            ->handle(Argument::type(UnsubscribeProductCommand::class))
            ->shouldNotBeCalled();

        $this->onPreSave($event);
    }

    public function it_is_not_applied_if_product_family_is_set(
        GenericEvent $event,
        ProductInterface $product,
        FamilyInterface $family,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $product->getFamily()->willReturn($family);

        $unsubscribeProductHandler
            ->handle(Argument::type(UnsubscribeProductCommand::class))
            ->shouldNotBeCalled();

        $this->onPreSave($event);
    }

    public function it_does_not_unsubscribe_the_product_if_previous_family_was_null(
        GenericEvent $event,
        ProductInterface $product,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        $selectProductFamilyIdQuery
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $product->getFamily()->willReturn(null);
        $selectProductFamilyIdQuery->execute(1)->willReturn(null);

        $unsubscribeProductHandler
            ->handle(Argument::type(UnsubscribeProductCommand::class))
            ->shouldNotBeCalled();

        $this->onPreSave($event);
    }

    public function it_unsubscribes_the_product_when_product_has_a_family_removed(
        GenericEvent $event,
        ProductInterface $product,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        $selectProductFamilyIdQuery
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $product->getFamily()->willReturn(null);
        $selectProductFamilyIdQuery->execute(1)->willReturn(42);

        $unsubscribeProductHandler->handle(new UnsubscribeProductCommand(1))->shouldBeCalled();

        $this->onPreSave($event);
    }
}
