<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Family;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyUpdatedSubscriberSpec extends ObjectBehavior
{
    public function let(GetConnectionIsActiveHandler $connectionIsActiveHandler, PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->beConstructedWith($connectionIsActiveHandler, $pendingItemsRepository);
    }

    public function it_is_only_applied_on_post_save_event_when_a_family_is_updated(
        GenericEvent $event,
        \stdClass $object,
        $connectionIsActiveHandler
    ): void {
        $event->getSubject()->willReturn($object);
        $connectionIsActiveHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_is_only_applied_on_post_save_when_franklin_insights_is_activated(
        GenericEvent $event,
        FamilyInterface $family,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $event->getSubject()->willReturn($family);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(false);
        $pendingItemsRepository->addUpdatedFamilyCode(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_saves_the_updated_family_code(
        GenericEvent $event,
        FamilyInterface $family,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $family->getCode()->willReturn('headphones');
        $event->getSubject()->willReturn($family);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);
        $pendingItemsRepository->addUpdatedFamilyCode('headphones')->shouldBeCalled();

        $this->onSave($event);
    }

    public function it_saves_multiple_updated_family_codes(
        GenericEvent $event,
        FamilyInterface $family1,
        FamilyInterface $family2,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $family1->getCode()->willReturn('headphones');
        $family2->getCode()->willReturn('router');
        $event->getSubject()->willReturn([$family1, $family2]);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);
        $pendingItemsRepository->addUpdatedFamilyCode('headphones')->shouldBeCalled();
        $pendingItemsRepository->addUpdatedFamilyCode('router')->shouldBeCalled();

        $this->onSaveAll($event);
    }
}
