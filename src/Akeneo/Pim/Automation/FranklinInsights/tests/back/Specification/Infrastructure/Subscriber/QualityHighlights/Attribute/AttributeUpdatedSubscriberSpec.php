<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Attribute;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeUpdatedSubscriberSpec extends ObjectBehavior
{
    public function let(GetConnectionIsActiveHandler $connectionIsActiveHandler, PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->beConstructedWith($connectionIsActiveHandler, $pendingItemsRepository);
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

    public function it_is_only_applied_on_post_save_event_when_an_attribute_is_updated(
        GenericEvent $event,
        \stdClass $object,
        $connectionIsActiveHandler
    ): void {
        $event->getSubject()->willReturn($object);
        $connectionIsActiveHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_is_only_applied_if_attribute_type_is_handled(
        GenericEvent $event,
        AttributeInterface $attribute,
        $connectionIsActiveHandler
    ): void {
        $event->getSubject()->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $connectionIsActiveHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_is_only_applied_on_post_save_when_franklin_insights_is_activated(
        GenericEvent $event,
        AttributeInterface $attribute,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $event->getSubject()->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(false);
        $pendingItemsRepository->addUpdatedAttributeCode(Argument::any())->shouldNotBeCalled();

        $this->onSave($event);
    }

    public function it_saves_the_updated_attribute_code(
        GenericEvent $event,
        AttributeInterface $attribute,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $attribute->getCode()->willReturn('size');
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $event->getSubject()->willReturn($attribute);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);
        $pendingItemsRepository->addUpdatedAttributeCode('size')->shouldBeCalled();

        $this->onSave($event);
    }

    public function it_saves_multiple_updated_attribute_codes(
        GenericEvent $event,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $attribute1->getCode()->willReturn('size');
        $attribute1->getType()->willReturn(AttributeTypes::TEXT);
        $attribute2->getCode()->willReturn('weight');
        $attribute2->getType()->willReturn(AttributeTypes::TEXT);
        $event->getSubject()->willReturn([$attribute1, $attribute2]);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);
        $pendingItemsRepository->addUpdatedAttributeCode('size')->shouldBeCalled();
        $pendingItemsRepository->addUpdatedAttributeCode('weight')->shouldBeCalled();

        $this->onSaveAll($event);
    }
}
