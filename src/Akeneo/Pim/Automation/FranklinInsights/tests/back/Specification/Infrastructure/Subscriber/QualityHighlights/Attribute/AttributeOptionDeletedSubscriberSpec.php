<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Attribute;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeOptionDeletedSubscriberSpec extends ObjectBehavior
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

    public function it_is_only_applied_when_an_attribute_option_is_removed(
        GenericEvent $event,
        $connectionIsActiveHandler
    ): void {
        $event->getSubject()->willReturn(new \stdClass());
        $connectionIsActiveHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }

    public function it_is_only_applied_when_franklin_insights_is_activated(
        GenericEvent $event,
        AttributeOptionInterface $attributeOption,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $event->getSubject()->willReturn($attributeOption);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(false);
        $pendingItemsRepository->addUpdatedAttributeCode(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove($event);
    }

    public function it_saves_the_option_attribute_code(
        GenericEvent $event,
        AttributeInterface $attribute,
        AttributeOptionInterface $attributeOption,
        $connectionIsActiveHandler,
        $pendingItemsRepository
    ): void {
        $attributeOption->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('size');
        $event->getSubject()->willReturn($attributeOption);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);
        $pendingItemsRepository->addUpdatedAttributeCode('size')->shouldBeCalled();

        $this->onPostRemove($event);
    }
}
