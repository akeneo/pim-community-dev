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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UpdateSubscriptionFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Product\ProductFamilyUpdateSubscriber;
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
class ProductFamilyUpdateSubscriberSpec extends ObjectBehavior
{
    public function let(
        SelectProductFamilyIdQuery $selectProductFamilyIdQuery,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler
    ): void {
        $this->beConstructedWith(
            $selectProductFamilyIdQuery,
            $unsubscribeProductHandler,
            $updateSubscriptionFamilyHandler
        );
    }

    public function it_is_a_product_family_update_subscriber(): void
    {
        $this->shouldHaveType(ProductFamilyUpdateSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_pre_save_event(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_SAVE);
    }

    public function it_is_only_applied_when_event_subject_is_a_product(
        GenericEvent $event,
        FamilyInterface $family,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler
    ): void {
        $event->getSubject()->willReturn($family);
        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPreSave($event);
    }

    public function it_is_not_applied_on_product_creation(
        GenericEvent $event,
        ProductInterface $product,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(null);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPreSave($event);
    }

    public function it_does_not_do_anything_if_previous_family_was_null(
        GenericEvent $event,
        ProductInterface $product,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler,
        $selectProductFamilyIdQuery
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $product->getFamily()->willReturn(null);
        $selectProductFamilyIdQuery->execute(1)->willReturn(null);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPreSave($event);
    }

    public function it_does_not_unsubscribe_if_product_family_is_set(
        GenericEvent $event,
        ProductInterface $product,
        FamilyInterface $family,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $product->getFamily()->willReturn($family);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();

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

    public function it_does_not_update_the_subscription_family_if_the_product_family_has_not_changed(
        GenericEvent $event,
        ProductInterface $product,
        FamilyInterface $family,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler,
        $selectProductFamilyIdQuery
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(42);
        $product->getFamily()->willReturn($family);
        $family->getId()->willReturn(56);
        $selectProductFamilyIdQuery->execute(42)->willReturn(56);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler->handle(Argument::cetera())->shouldNotBeCalled();

        $this->onPreSave($event);
    }

    public function it_updates_the_subscription_family_if_the_product_family_has_changed(
        GenericEvent $event,
        ProductInterface $product,
        FamilyInterface $family,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler,
        $selectProductFamilyIdQuery
    ): void {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(42);
        $product->getFamily()->willReturn($family);
        $family->getId()->willReturn(144);
        $selectProductFamilyIdQuery->execute(42)->willReturn(56);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler
            ->handle(new UpdateSubscriptionFamilyCommand(42, $family->getWrappedObject()))
            ->shouldBeCalled();

        $this->onPreSave($event);
    }
}
