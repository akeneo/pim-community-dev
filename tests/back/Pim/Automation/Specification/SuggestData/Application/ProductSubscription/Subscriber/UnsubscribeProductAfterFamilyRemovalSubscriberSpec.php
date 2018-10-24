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
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\DoesPersistedProductHaveFamilyInterface;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Subscriber\UnsubscribeProductAfterFamilyRemovalSubscriber;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class UnsubscribeProductAfterFamilyRemovalSubscriberSpec extends ObjectBehavior
{
    public function let(
        DoesPersistedProductHaveFamilyInterface $didProductLoseItsFamily,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ): void {
        $this->beConstructedWith($didProductLoseItsFamily, $unsubscribeProductHandler);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(UnsubscribeProductAfterFamilyRemovalSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_pre_save_storage_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE => 'unsubscribeSingleProduct',
            StorageEvents::PRE_SAVE_ALL => 'unsubscribeMultipleProducts',
        ]);
    }

    public function it_does_nothing_on_a_single_product_if_the_event_is_not_unitary(
        $didProductLoseItsFamily,
        $unsubscribeProductHandler
    ): void {
        $product = new Product();
        $product->setId(42);
        $event = new GenericEvent($product, ['unitary' => false]);

        $didProductLoseItsFamily->check(Argument::any())->shouldNotBeCalled();
        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->unsubscribeSingleProduct($event);
    }

    public function it_does_nothing_if_the_event_does_not_contain_a_product(
        $didProductLoseItsFamily,
        $unsubscribeProductHandler
    ): void {
        $event = new GenericEvent(new \StdClass(), ['unitary' => true]);

        $didProductLoseItsFamily->check(Argument::any())->shouldNotBeCalled();
        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->unsubscribeSingleProduct($event);
    }

    public function it_unsubscribes_a_product_that_lost_its_family(
        $didProductLoseItsFamily,
        $unsubscribeProductHandler
    ): void {
        $product = new Product();
        $product->setId(42);
        $event = new GenericEvent($product, ['unitary' => true]);

        $didProductLoseItsFamily->check($product)->willReturn(true);

        $command = new UnsubscribeProductCommand(42);
        $unsubscribeProductHandler->handle($command)->shouldBeCalled();

        $this->unsubscribeSingleProduct($event);
    }

    public function it_does_not_unsubscribe_a_new_product($didProductLoseItsFamily): void
    {
        $product = new Product();
        $event = new GenericEvent($product, ['unitary' => true]);

        $didProductLoseItsFamily->check(Argument::any())->shouldNotBeCalled();

        $this->unsubscribeSingleProduct($event);
    }

    public function it_does_not_unsubscribe_a_product_that_has_a_family($didProductLoseItsFamily): void
    {
        $family = new Family();
        $product = new Product();
        $product->setId(42);
        $product->setFamily($family);
        $event = new GenericEvent($product, ['unitary' => true]);

        $didProductLoseItsFamily->check(Argument::any())->shouldNotBeCalled();

        $this->unsubscribeSingleProduct($event);
    }

    public function it_does_not_unsubscribe_a_product_that_is_not_subscribed(
        $didProductLoseItsFamily,
        $unsubscribeProductHandler
    ): void {
        $product = new Product();
        $product->setId(42);
        $event = new GenericEvent($product, ['unitary' => true]);

        $didProductLoseItsFamily->check($product)->willReturn(true);

        $command = new UnsubscribeProductCommand(42);
        $unsubscribeProductHandler->handle($command)->willThrow(ProductSubscriptionException::class);

        $this->shouldNotThrow(\Exception::class)->during('unsubscribeSingleProduct', [$event]);
    }

    public function it_does_nothing_on_multiple_products_if_the_event_is_unitary(
        $didProductLoseItsFamily,
        $unsubscribeProductHandler
    ): void {
        $ultimateProduct = new Product();
        $ultimateProduct->setId(42);
        $evilProduct = new Product();
        $evilProduct->setId(666);
        $event = new GenericEvent([$ultimateProduct, $evilProduct], ['unitary' => true]);

        $didProductLoseItsFamily->check(Argument::any())->shouldNotBeCalled();
        $unsubscribeProductHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->unsubscribeSingleProduct($event);
    }

    public function it_unsubscribes_several_products_that_lost_their_family(
        $didProductLoseItsFamily,
        $unsubscribeProductHandler
    ): void {
        $ultimateProduct = new Product();
        $ultimateProduct->setId(42);
        $evilProduct = new Product();
        $evilProduct->setId(666);
        $event = new GenericEvent([$ultimateProduct, $evilProduct], ['unitary' => false]);

        $didProductLoseItsFamily->check($ultimateProduct)->willReturn(true);
        $didProductLoseItsFamily->check($evilProduct)->willReturn(true);

        $command = new UnsubscribeProductCommand(42);
        $unsubscribeProductHandler->handle($command)->shouldBeCalled();
        $command = new UnsubscribeProductCommand(666);
        $unsubscribeProductHandler->handle($command)->shouldBeCalled();

        $this->unsubscribeMultipleProducts($event);
    }
}
