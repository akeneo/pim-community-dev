<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Family;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\RemoveAttributesFromMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\FamilyAttribute\Query\FindFamilyAttributesNotInQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Family\FamilyAttributesRemoveSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyAttributesRemoveSubscriberSpec extends ObjectBehavior
{
    public function let(
        FindFamilyAttributesNotInQueryInterface $query,
        RemoveAttributesFromMappingInterface $removeAttributesFromMapping
    ): void {
        $this->beConstructedWith($query, $removeAttributesFromMapping);
    }

    public function it_is_a_family_subscriber(): void
    {
        $this->shouldHaveType(FamilyAttributesRemoveSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_events(): void
    {
        $this::getSubscribedEvents()->shouldReturn([StorageEvents::PRE_SAVE => 'updateAttributesMapping']);
    }

    public function it_updates_attributes_mapping_linked_to_the_removed_attributes_from_the_family(
        $query,
        $removeAttributesFromMapping,
        GenericEvent $event,
        FamilyInterface $family
    ): void {
        $event->getSubject()->willReturn($family);

        $family->getId()->willReturn(2);
        $family->getCode()->willReturn('shoes');
        $family->getAttributeCodes()->willReturn(['sku', 'name', 'description', 'size', 'color']);

        $removedAttributes = ['brand', 'type'];
        $query
            ->findFamilyAttributesNotIn('shoes', ['sku', 'name', 'description', 'size', 'color'])
            ->willReturn($removedAttributes);

        $removeAttributesFromMapping
            ->process(['shoes'], $removedAttributes)
            ->shouldBeCalled();

        $this->updateAttributesMapping($event);
    }

    public function it_does_nothing_if_event_does_not_come_from_a_family(
        $query,
        $removeAttributesFromMapping,
        GenericEvent $event,
        ProductInterface $product
    ): void {
        $event->getSubject()->willReturn($product);

        $query->findFamilyAttributesNotIn(Argument::cetera())->shouldNotBeCalled();
        $removeAttributesFromMapping->process(Argument::cetera())->shouldNotBeCalled();

        $this->updateAttributesMapping($event)->shouldReturn(null);
    }

    public function it_does_nothing_if_event_comes_from_a_newly_created_family(
        $query,
        $removeAttributesFromMapping,
        GenericEvent $event,
        FamilyInterface $family
    ): void {
        $event->getSubject()->willReturn($family);

        $family->getId()->willReturn(null);

        $query->findFamilyAttributesNotIn(Argument::cetera())->shouldNotBeCalled();
        $removeAttributesFromMapping->process(Argument::cetera())->shouldNotBeCalled();

        $this->updateAttributesMapping($event)->shouldReturn(null);
    }
}
