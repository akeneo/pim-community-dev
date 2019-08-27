<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeUpdatedSubscriberSpec extends ObjectBehavior
{
    public function let(GetConnectionStatusHandler $connectionStatusHandler, PendingItemsRepositoryInterface $pendingAttributesRepository)
    {
        $this->beConstructedWith($connectionStatusHandler, $pendingAttributesRepository);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_post_save_events(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE_ALL);
    }

    public function it_is_only_applied_on_post_save_event_when_an_attribute_is_removed(
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
        AttributeInterface $attribute,
        $connectionStatusHandler,
        $pendingAttributesRepository
    ): void {
        $event->getSubject()->willReturn($attribute);

        $connectionStatus = new ConnectionStatus(false, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingAttributesRepository->addUpdatedAttributeCode(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_saves_the_updated_attribute_code(
        GenericEvent $event,
        AttributeInterface $attribute,
        $connectionStatusHandler,
        $pendingAttributesRepository
    ): void {
        $attribute->getCode()->willReturn('size');
        $event->getSubject()->willReturn($attribute);

        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingAttributesRepository->addUpdatedAttributeCode('size')->shouldBeCalled();

        $this->onSave($event);
    }

    public function it_saves_multiple_updated_attribute_codes(
        GenericEvent $event,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        $connectionStatusHandler,
        $pendingAttributesRepository
    ): void {
        $attribute1->getCode()->willReturn('size');
        $attribute2->getCode()->willReturn('weight');
        $event->getSubject()->willReturn([$attribute1, $attribute2]);

        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
        $pendingAttributesRepository->addUpdatedAttributeCode('size')->shouldBeCalled();
        $pendingAttributesRepository->addUpdatedAttributeCode('weight')->shouldBeCalled();

        $this->onSaveAll($event);
    }
}
