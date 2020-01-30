<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Product;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductUpdatedSubscriberSpec extends ObjectBehavior
{
    public function let(GetConnectionIsActiveHandler $connectionIsActiveHandler, PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->beConstructedWith($connectionIsActiveHandler, $pendingItemsRepository);
    }

    public function it_is_only_applied_on_post_save_event_when_a_product_is_updated(
        GenericEvent $event,
        \stdClass $object,
        $connectionIsActiveHandler
    ): void {
        $event->getSubject()->willReturn($object);
        $connectionIsActiveHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_is_only_applied_on_post_save_event_when_a_product_is_not_variant(
        GenericEvent $event,
        ProductInterface $product,
        $connectionIsActiveHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(true);
        $connectionIsActiveHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_is_only_applied_on_post_save_when_franklin_insights_is_activated(
        GenericEvent $event,
        ProductInterface $product,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(false);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(false);
        $pendingItemsRepository->addUpdatedProductId(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_saves_the_updated_product_id(
        GenericEvent $event,
        ProductInterface $product,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $product->getId()->willReturn(42);
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(false);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);
        $pendingItemsRepository->addUpdatedProductId(42)->shouldBeCalled();

        $this->onSave($event);
    }

    public function it_saves_multiple_updated_product_ids(
        GenericEvent $event,
        ProductInterface $product1,
        ProductInterface $product2,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $product1->getId()->willReturn(42);
        $product1->isVariant()->willReturn(false);
        $product2->getId()->willReturn(39);
        $product2->isVariant()->willReturn(false);
        $event->getSubject()->willReturn([$product1, $product2]);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);
        $pendingItemsRepository->addUpdatedProductId(42)->shouldBeCalled();
        $pendingItemsRepository->addUpdatedProductId(39)->shouldBeCalled();

        $this->onSaveAll($event);
    }
}
