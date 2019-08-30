<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Product;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductUpdatedSubscriberSpec extends ObjectBehavior
{
    public function let(GetConnectionStatusHandler $connectionStatusHandler, PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->beConstructedWith($connectionStatusHandler, $pendingItemsRepository);
    }

    public function it_is_only_applied_on_post_save_event_when_a_product_is_updated(
        GenericEvent $event,
        \stdClass $object,
        $connectionStatusHandler
    ): void {
        $event->getSubject()->willReturn($object);
        $connectionStatusHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_is_only_applied_on_post_save_event_when_a_product_is_not_variant(
        GenericEvent $event,
        ProductInterface $product,
        $connectionStatusHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(true);
        $connectionStatusHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_is_only_applied_on_post_save_when_franklin_insights_is_activated(
        GenericEvent $event,
        ProductInterface $product,
        $connectionStatusHandler,
        $pendingItemsRepository
    ): void {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(false);

        $connectionStatus = new ConnectionStatus(false, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingItemsRepository->addUpdatedProductIdentifier(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_saves_the_updated_product_id(
        GenericEvent $event,
        ProductInterface $product,
        $connectionStatusHandler,
        $pendingItemsRepository
    ): void {
        $product->getId()->willReturn(42);
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(false);

        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingItemsRepository->addUpdatedProductIdentifier(42)->shouldBeCalled();

        $this->onSave($event);
    }

    public function it_saves_multiple_updated_product_ids(
        GenericEvent $event,
        ProductInterface $product1,
        ProductInterface $product2,
        $connectionStatusHandler,
        $pendingItemsRepository
    ): void {
        $product1->getId()->willReturn(42);
        $product1->isVariant()->willReturn(false);
        $product2->getId()->willReturn(39);
        $product2->isVariant()->willReturn(false);
        $event->getSubject()->willReturn([$product1, $product2]);

        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingItemsRepository->addUpdatedProductIdentifier(42)->shouldBeCalled();
        $pendingItemsRepository->addUpdatedProductIdentifier(39)->shouldBeCalled();

        $this->onSaveAll($event);
    }
}
