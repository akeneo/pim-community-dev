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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Family;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\RemoveAttributesFromMappingInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Query\SelectRemovedFamilyAttributeCodesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Family\FamilyAttributesRemoveSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

class FamilyAttributesRemoveSubscriberSpec extends ObjectBehavior
{
    public function let(
        SelectRemovedFamilyAttributeCodesQueryInterface $selectRemovedFamilyAttributeCodesQuery,
        RemoveAttributesFromMappingInterface $removeAttributesFromMapping
    ): void {
        $this->beConstructedWith($selectRemovedFamilyAttributeCodesQuery, $removeAttributesFromMapping);
    }

    public function it_is_a_family_subscriber(): void
    {
        $this->shouldHaveType(FamilyAttributesRemoveSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_events(): void
    {
        Assert::eq(
            [StorageEvents::PRE_SAVE, StorageEvents::POST_SAVE],
            array_keys($this::getSubscribedEvents()->getWrappedObject())
        );
    }

    public function it_finds_attributes_removed_from_the_family(
        $selectRemovedFamilyAttributeCodesQuery,
        GenericEvent $event,
        FamilyInterface $family
    ): void {
        $event->getSubject()->willReturn($family);

        $family->getId()->willReturn(2);
        $family->getCode()->willReturn('shoes');
        $family->getAttributeCodes()->willReturn(['sku', 'name', 'description', 'size', 'color']);

        $removedAttributes = ['brand', 'type'];
        $selectRemovedFamilyAttributeCodesQuery
            ->execute('shoes', ['sku', 'name', 'description', 'size', 'color'])
            ->willReturn($removedAttributes);

        $this->onFamilyAttributesRemoved($event);
    }

    public function it_updates_attributes_mapping_when_there_are_some_attributes_removed_from_the_family(
        $selectRemovedFamilyAttributeCodesQuery,
        $removeAttributesFromMapping,
        GenericEvent $event,
        FamilyInterface $family
    ): void {
        $event->getSubject()->willReturn($family);

        $family->getId()->willReturn(2);
        $family->getCode()->willReturn('shoes');
        $family->getAttributeCodes()->willReturn(['sku', 'name', 'size', 'color']);

        $removedAttributeCodes = ['brand', 'type'];
        $selectRemovedFamilyAttributeCodesQuery
            ->execute('shoes', ['sku', 'name', 'size', 'color'])
            ->willReturn($removedAttributeCodes);

        $this->onFamilyAttributesRemoved($event);

        $removeAttributesFromMapping->process(['shoes'], $removedAttributeCodes)->shouldBeCalled();

        $this->updateAttributesMapping($event);
    }

    public function it_does_nothing_if_event_does_not_come_from_a_family(
        $selectRemovedFamilyAttributeCodesQuery,
        $removeAttributesFromMapping,
        GenericEvent $event,
        ProductInterface $product
    ): void {
        $event->getSubject()->willReturn($product);

        $selectRemovedFamilyAttributeCodesQuery->execute(Argument::cetera())->shouldNotBeCalled();
        $removeAttributesFromMapping->process(Argument::cetera())->shouldNotBeCalled();

        $this->onFamilyAttributesRemoved($event)->shouldReturn(null);
        $this->updateAttributesMapping($event)->shouldReturn(null);
    }

    public function it_does_nothing_if_event_comes_from_a_newly_created_family(
        $selectRemovedFamilyAttributeCodesQuery,
        $removeAttributesFromMapping,
        GenericEvent $event,
        FamilyInterface $family
    ): void {
        $event->getSubject()->willReturn($family);

        $family->getId()->willReturn(null);

        $selectRemovedFamilyAttributeCodesQuery->execute(Argument::cetera())->shouldNotBeCalled();
        $removeAttributesFromMapping->process(Argument::cetera())->shouldNotBeCalled();

        $this->onFamilyAttributesRemoved($event)->shouldReturn(null);
        $this->updateAttributesMapping($event)->shouldReturn(null);
    }

    public function it_does_not_launch_job_when_there_are_no_attribute_codes_removed_from_the_family(
        $selectRemovedFamilyAttributeCodesQuery,
        $removeAttributesFromMapping,
        GenericEvent $event,
        FamilyInterface $family
    ): void {
        $event->getSubject()->willReturn($family);

        $family->getId()->willReturn(2);
        $family->getCode()->willReturn('shoes');
        $family->getAttributeCodes()->willReturn(['sku', 'name', 'size', 'color']);

        $selectRemovedFamilyAttributeCodesQuery
            ->execute('shoes', ['sku', 'name', 'size', 'color'])
            ->willReturn([]);

        $this->onFamilyAttributesRemoved($event);

        $removeAttributesFromMapping->process(['shoes'], [])->shouldNotBeCalled();

        $this->updateAttributesMapping($event);
    }
}
