<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Family;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyUpdatedSubscriberSpec extends ObjectBehavior
{
    public function let(GetConnectionStatusHandler $connectionStatusHandler, PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->beConstructedWith($connectionStatusHandler, $pendingItemsRepository);
    }

    public function it_is_only_applied_on_post_save_event_when_a_family_is_updated(
        GenericEvent $event,
        \stdClass $object,
        $connectionStatusHandler
    ): void {
        $event->getSubject()->willReturn($object);
        $connectionStatusHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_is_only_applied_on_post_save_when_franklin_insights_is_activated(
        GenericEvent $event,
        FamilyInterface $family,
        $connectionStatusHandler,
        $pendingItemsRepository
    ): void {
        $event->getSubject()->willReturn($family);

        $connectionStatus = new ConnectionStatus(false, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingItemsRepository->addUpdatedFamilyCode(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_saves_the_updated_family_code(
        GenericEvent $event,
        FamilyInterface $family,
        $connectionStatusHandler,
        $pendingItemsRepository
    ): void {
        $family->getCode()->willReturn('headphones');
        $event->getSubject()->willReturn($family);

        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingItemsRepository->addUpdatedFamilyCode('headphones')->shouldBeCalled();

        $this->onSave($event);
    }

    public function it_saves_multiple_updated_family_codes(
        GenericEvent $event,
        FamilyInterface $family1,
        FamilyInterface $family2,
        $connectionStatusHandler,
        $pendingItemsRepository
    ): void {
        $family1->getCode()->willReturn('headphones');
        $family2->getCode()->willReturn('router');
        $event->getSubject()->willReturn([$family1, $family2]);

        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingItemsRepository->addUpdatedFamilyCode('headphones')->shouldBeCalled();
        $pendingItemsRepository->addUpdatedFamilyCode('router')->shouldBeCalled();

        $this->onSaveAll($event);
    }
}
