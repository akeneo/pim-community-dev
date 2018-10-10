<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Bundle\EventSubscriber\AddUniqueAttributesToVariantProductAttributeSetSubscriber;
use Akeneo\Pim\Structure\Component\FamilyVariant\AddUniqueAttributes;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddUniqueAttributesToVariantProductAttributeSetSubscriberSpec extends ObjectBehavior
{
    function let(AddUniqueAttributes $addUniqueAttributes)
    {
        $this->beConstructedWith($addUniqueAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddUniqueAttributesToVariantProductAttributeSetSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_listen_to_storage_pre_save_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE => 'addUniqueAttributes',
        ]);
    }

    function it_does_not_add_unique_attributes_from_unsupported_entity(
        $addUniqueAttributes,
        GenericEvent $event,
        CategoryInterface $category
    ) {
        $event->getSubject()->willReturn($category);

        $addUniqueAttributes->addToFamilyVariant(Argument::any())->shouldNotBeCalled();

        $this->addUniqueAttributes($event);
    }

    function it_adds_unique_attributes_for_family_variant(
        $addUniqueAttributes,
        GenericEvent $event,
        FamilyVariantInterface $familyVariant
    ) {
        $event->getSubject()->willReturn($familyVariant);

        $addUniqueAttributes->addToFamilyVariant($familyVariant)->shouldBeCalled();

        $this->addUniqueAttributes($event);
    }

    function it_adds_unique_attributes_for_family(
        $addUniqueAttributes,
        GenericEvent $event,
        FamilyVariantInterface $familyVariant,
        ArrayCollection $familyVariants,
        \Iterator $familyVariantsIterator,
        FamilyInterface $family
    ) {
        $event->getSubject()->willReturn($family);
        $family->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->isEmpty()->willReturn(false);

        $familyVariants->getIterator()->willReturn($familyVariantsIterator);
        $familyVariantsIterator->rewind()->shouldBeCalled();
        $familyVariantsIterator->valid()->willReturn(true, false);
        $familyVariantsIterator->current()->willReturn($familyVariant);
        $familyVariantsIterator->next()->shouldBeCalled();

        $addUniqueAttributes->addToFamilyVariant($familyVariant)->shouldBeCalled();

        $this->addUniqueAttributes($event);
    }

    function it_does_not_add_unique_attributes_for_family_without_variation(
        $addUniqueAttributes,
        GenericEvent $event,
        ArrayCollection $familyVariants,
        FamilyInterface $family
    ) {
        $event->getSubject()->willReturn($family);
        $family->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->isEmpty()->willReturn(true);

        $addUniqueAttributes->addToFamilyVariant(Argument::any())->shouldNotBeCalled();

        $this->addUniqueAttributes($event);
    }
}
