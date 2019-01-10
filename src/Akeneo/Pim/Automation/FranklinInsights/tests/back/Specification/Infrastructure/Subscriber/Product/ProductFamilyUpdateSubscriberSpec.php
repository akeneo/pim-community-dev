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
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Product\SelectProductFamilyIdQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Product\ProductFamilyUpdateSubscriber;
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
        UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler,
        GetConnectionStatusHandler $connectionStatusHandler
    ): void {
        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);

        $this->beConstructedWith(
            $selectProductFamilyIdQuery,
            $unsubscribeProductHandler,
            $updateSubscriptionFamilyHandler,
            $connectionStatusHandler
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

    public function it_subscribes_pre_save_and_post_save_events(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_SAVE);
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
    }

    public function it_is_only_applied_when_event_subject_is_a_product(
        $unsubscribeProductHandler,
        $updateSubscriptionFamilyHandler,
        FamilyInterface $family
    ): void {
        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPreSave(new GenericEvent($family->getWrappedObject()));
        $this->onPostSave(new GenericEvent($family->getWrappedObject()));
    }

    public function it_is_not_applied_on_product_creation(
        $selectProductFamilyIdQuery,
        $unsubscribeProductHandler,
        $updateSubscriptionFamilyHandler,
        ProductInterface $product,
        ProductInterface $savedProduct
    ): void {
        $product->getId()->willReturn(null);
        $savedProduct->getId()->willReturn(42);

        $selectProductFamilyIdQuery->execute(Argument::any())->shouldNotBeCalled();
        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPreSave(new GenericEvent($product->getWrappedObject()));
        $this->onPostSave(new GenericEvent($savedProduct->getWrappedObject()));
    }

    public function it_is_not_applied_if_franklin_insights_is_not_activated(
        $selectProductFamilyIdQuery,
        $unsubscribeProductHandler,
        $updateSubscriptionFamilyHandler,
        $connectionStatusHandler,
        ProductInterface $product,
        ProductInterface $savedProduct
    ): void {
        $product->getId()->willReturn(42);
        $savedProduct->getId()->willReturn(42);

        $connectionStatus = new ConnectionStatus(false, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);

        $selectProductFamilyIdQuery->execute(Argument::any())->shouldNotBeCalled();
        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPreSave(new GenericEvent($product->getWrappedObject()));
        $this->onPostSave(new GenericEvent($savedProduct->getWrappedObject()));
    }

    public function it_does_not_do_anything_if_previous_family_was_null(
        $selectProductFamilyIdQuery,
        $unsubscribeProductHandler,
        $updateSubscriptionFamilyHandler,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(1);
        $product->getFamily()->willReturn(null);
        $selectProductFamilyIdQuery->execute(1)->willReturn(null);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPreSave(new GenericEvent($product->getWrappedObject()));
        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_unsubscribes_the_product_when_product_has_a_family_removed(
        $selectProductFamilyIdQuery,
        $unsubscribeProductHandler,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(1);
        $product->getFamily()->willReturn(null);
        $selectProductFamilyIdQuery->execute(1)->willReturn(42);

        $unsubscribeProductHandler->handle(new UnsubscribeProductCommand(1))->shouldBeCalled();

        $this->onPreSave(new GenericEvent($product->getWrappedObject()));
        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_silently_fails_during_unsubscription_if_the_product_is_not_subscribed(
        $selectProductFamilyIdQuery,
        $unsubscribeProductHandler,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(1);
        $product->getFamily()->willReturn(null);
        $selectProductFamilyIdQuery->execute(1)->willReturn(42);

        $unsubscribeProductHandler->handle(new UnsubscribeProductCommand(1))->willThrow(
            ProductNotSubscribedException::class
        );

        $this->onPreSave(new GenericEvent($product->getWrappedObject()));
        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_does_not_update_the_subscription_family_if_the_product_family_has_not_changed(
        $selectProductFamilyIdQuery,
        $unsubscribeProductHandler,
        $updateSubscriptionFamilyHandler,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->getId()->willReturn(42);
        $product->getFamily()->willReturn($family);
        $family->getId()->willReturn(56);
        $selectProductFamilyIdQuery->execute(42)->willReturn(56);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler->handle(Argument::cetera())->shouldNotBeCalled();

        $this->onPreSave(new GenericEvent($product->getWrappedObject()));
        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_updates_the_subscription_family_if_the_product_family_has_changed(
        $selectProductFamilyIdQuery,
        $unsubscribeProductHandler,
        $updateSubscriptionFamilyHandler,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->getId()->willReturn(42);
        $product->getFamily()->willReturn($family);
        $family->getId()->willReturn(144);
        $selectProductFamilyIdQuery->execute(42)->willReturn(56);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler
            ->handle(new UpdateSubscriptionFamilyCommand(42, $family->getWrappedObject()))
            ->shouldBeCalled();

        $this->onPreSave(new GenericEvent($product->getWrappedObject()));
        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_silently_fails_during_subscription_update_if_product_is_not_subscribed(
        $selectProductFamilyIdQuery,
        $unsubscribeProductHandler,
        $updateSubscriptionFamilyHandler,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->getId()->willReturn(42);
        $product->getFamily()->willReturn($family);
        $family->getId()->willReturn(144);
        $selectProductFamilyIdQuery->execute(42)->willReturn(56);

        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();
        $updateSubscriptionFamilyHandler
            ->handle(new UpdateSubscriptionFamilyCommand(42, $family->getWrappedObject()))
            ->willThrow(ProductNotSubscribedException::class);

        $this->onPreSave(new GenericEvent($product->getWrappedObject()));
        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }
}
