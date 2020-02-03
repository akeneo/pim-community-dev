<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Product;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductDeletedSubscriberSpec extends ObjectBehavior
{
    public function let(GetConnectionIsActiveHandler $connectionIsActiveHandler, PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->beConstructedWith($connectionIsActiveHandler, $pendingItemsRepository);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_post_remove(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
    }

    public function it_is_only_applied_when_a_product_is_removed(
        GenericEvent $event,
        $connectionIsActiveHandler
    ): void {
        $event->getSubject()->willReturn(new \stdClass());
        $connectionIsActiveHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }

    public function it_is_only_applied_when_a_product_no_variant_is_removed(
        GenericEvent $event,
        ProductInterface $product,
        $connectionIsActiveHandler
    ): void {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(true);
        $connectionIsActiveHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }

    public function it_is_only_applied_when_franklin_insights_is_activated(
        GenericEvent $event,
        ProductInterface $product,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(false);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(false);
        $pendingItemsRepository->addDeletedProductId(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }

    public function it_saves_the_deleted_product_identifier(
        GenericEvent $event,
        ProductInterface $product,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $product->getId()->willReturn(42);
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(false);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);
        $pendingItemsRepository->addDeletedProductId(42)->shouldBeCalled();

        $this->onPreRemove($event);
        $this->onPostRemove($event);
    }
}
