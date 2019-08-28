<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Family;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyDeletedSubscriberSpec extends ObjectBehavior
{
    public function let(GetConnectionStatusHandler $connectionStatusHandler, PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->beConstructedWith($connectionStatusHandler, $pendingItemsRepository);
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
        $connectionStatusHandler
    ): void {
        $event->getSubject()->willReturn(new \stdClass());
        $connectionStatusHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }

    public function it_is_only_applied_when_franklin_insights_is_activated(
        GenericEvent $event,
        FamilyInterface $family,
        $connectionStatusHandler,
        $pendingItemsRepository
    ): void {
        $event->getSubject()->willReturn($family);

        $connectionStatus = new ConnectionStatus(false, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingItemsRepository->addDeletedFamilyCode(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }

    public function it_saves_the_deleted_family_code(
        GenericEvent $event,
        FamilyInterface $family,
        $connectionStatusHandler,
        $pendingItemsRepository
    ): void {
        $family->getCode()->willReturn('headphones');
        $event->getSubject()->willReturn($family);

        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingItemsRepository->addDeletedFamilyCode('headphones')->shouldBeCalled();

        $this->onPostRemove($event);
    }
}
