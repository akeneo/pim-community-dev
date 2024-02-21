<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Bundle\EventSubscriber\RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriber;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriberSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE => 'removeDeletedAttributesFromFamilyVariants',
        ]);
    }

    function it_does_not_process_non_family_objects(GenericEvent $event, \StdClass $notFamilyObject)
    {
        $event->getSubject()->willReturn($notFamilyObject);
        $this->removeDeletedAttributesFromFamilyVariants($event);
    }

    function it_removes_attributes_from_a_family_variant_when_it_has_been_removed_from_the_family(
        GenericEvent $event,
        FamilyInterface $family,
        Collection $familyVariants,
        \ArrayIterator $familyVariantsIterator,
        FamilyVariantInterface $familyVariant,
        Collection $familyVariantAttributes,
        Collection $variantAttributeSets,
        \ArrayIterator $variantAttributeSetsIterator,
        Collection $variantAxes,
        VariantAttributeSetInterface $variantAttributeSet1,
        VariantAttributeSetInterface $variantAttributeSet2,
        Collection $familyVariantAttributes1,
        \ArrayIterator $familyVariantAttributesIterator1,
        Collection $familyVariantAttributes2,
        \ArrayIterator $familyVariantAttributesIterator2,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3,
        AttributeInterface $attribute4,
        AttributeInterface $axis1,
        AttributeInterface $axis2,
        AttributeInterface $toRemoveAttribute
    ) {
        $event->getSubject()->willReturn($family);

        $family->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->getIterator()->willReturn($familyVariantsIterator);
        $familyVariantsIterator->valid()->willReturn(true, false);
        $familyVariantsIterator->current()->willReturn($familyVariant);
        $familyVariantsIterator->rewind()->shouldBeCalled();
        $familyVariantsIterator->next()->shouldBeCalled();

        $familyVariant->getAttributes()->willReturn($familyVariantAttributes);
        $familyVariantAttributes->map(Argument::cetera())->willReturn($familyVariantAttributes);
        $familyVariantAttributes->toArray()->willReturn([
            'attribute_1',
            'attribute_2',
            'attribute_3',
            'attribute_4',
            'axis_1',
            'axis_2',
            'attribute_removed_from_family'
        ]);

        $familyVariant->getAxes()->willReturn($variantAxes);
        $variantAxes->map(Argument::cetera())->willReturn($variantAxes);
        $variantAxes->toArray()->willReturn(['axis_1', 'axis_2']);

        $toRemoveAttribute->getCode()->willReturn('attribute_removed_from_family');

        $family->getAttributeCodes()->willReturn([
            'attribute_1',
            'attribute_2',
            'attribute_3',
            'attribute_4',
            'axis_1',
            'axis_2'
        ]);

        $familyVariant->getVariantAttributeSets()->willReturn($variantAttributeSets);
        $variantAttributeSets->getIterator()->willReturn($variantAttributeSetsIterator);
        $variantAttributeSetsIterator->current()->willReturn($variantAttributeSet1, $variantAttributeSet2);
        $variantAttributeSetsIterator->valid()->willReturn(true, true, false);
        $variantAttributeSetsIterator->rewind()->shouldBeCalled();
        $variantAttributeSetsIterator->next()->shouldBeCalled();

        $variantAttributeSet1->getAttributes()->willReturn($familyVariantAttributes1);
        $familyVariantAttributes1->getIterator()->willReturn($familyVariantAttributesIterator1);
        $familyVariantAttributesIterator1->current()->willReturn($attribute1, $attribute2, $axis1, $toRemoveAttribute);
        $familyVariantAttributesIterator1->valid()->willReturn(true, true, true, true, false);
        $familyVariantAttributesIterator1->next()->shouldBeCalled();
        $familyVariantAttributesIterator1->rewind()->shouldBeCalled();

        $variantAttributeSet2->getAttributes()->willReturn($familyVariantAttributes2);
        $familyVariantAttributes2->getIterator()->willReturn($familyVariantAttributesIterator2);
        $familyVariantAttributesIterator2->current()->willReturn($attribute3, $attribute4, $axis2);
        $familyVariantAttributesIterator2->valid()->willReturn(true, true, true, false);
        $familyVariantAttributesIterator2->next()->shouldBeCalled();
        $familyVariantAttributesIterator2->rewind()->shouldBeCalled();

        $attribute1->getCode()->willReturn('attribute_1');
        $attribute2->getCode()->willReturn('attribute_2');
        $attribute3->getCode()->willReturn('attribute_3');
        $attribute4->getCode()->willReturn('attribute_4');
        $axis1->getCode()->willReturn('axis_1');
        $axis2->getCode()->willReturn('axis_2');

        $variantAttributeSet1->setAttributes([$attribute1, $attribute2, $axis1])->shouldBeCalled();
        $variantAttributeSet2->setAttributes([$attribute3, $attribute4, $axis2])->shouldBeCalled();

        $this->removeDeletedAttributesFromFamilyVariants($event);
    }
}
