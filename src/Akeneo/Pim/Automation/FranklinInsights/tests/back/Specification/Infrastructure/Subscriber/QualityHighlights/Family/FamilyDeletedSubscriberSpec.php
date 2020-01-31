<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Family;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyDeletedSubscriberSpec extends ObjectBehavior
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

    public function it_is_only_applied_when_a_family_is_removed(
        GenericEvent $event,
        $connectionIsActiveHandler
    ): void {
        $event->getSubject()->willReturn(new \stdClass());
        $connectionIsActiveHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }

    public function it_is_only_applied_when_franklin_insights_is_activated(
        GenericEvent $event,
        FamilyInterface $family,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $event->getSubject()->willReturn($family);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(false);
        $pendingItemsRepository->addDeletedFamilyCode(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }

    public function it_saves_the_deleted_family_code(
        GenericEvent $event,
        FamilyInterface $family,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $family->getCode()->willReturn('headphones');
        $event->getSubject()->willReturn($family);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);
        $pendingItemsRepository->addDeletedFamilyCode('headphones')->shouldBeCalled();

        $this->onPostRemove($event);
    }
}
