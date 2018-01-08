<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\EventSubscriber\SaveFamilyVariantOnFamilyUpdateSubscriber;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SaveFamilyVariantOnFamilyUpdateSubscriberSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        BulkSaverInterface $familyVariantSaver,
        BulkObjectDetacherInterface $objectDetacher
    )
    {
        $this->beConstructedWith($validator, $familyVariantSaver, $objectDetacher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SaveFamilyVariantOnFamilyUpdateSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE => 'validateAndSaveFamilyVariants',
        ]);
    }

    function it_only_supports_families_object(
        $validator,
        $familyVariantSaver,
        GenericEvent $event,
        \stdClass $object
    ) {
        $validator->validate()->shouldNotBeCalled();
        $familyVariantSaver->saveAll()->shouldNotBeCalled();

        $event->getSubject()->willReturn($object);
        $this->validateAndSaveFamilyVariants($event);
    }

    function it_validates_and_saves_family_variants_on_family_update(
        $validator,
        $familyVariantSaver,
        $objectDetacher,
        GenericEvent $event,
        FamilyInterface $family,
        Collection $familyVariants,
        \ArrayIterator $familyVariantsIterator,
        FamilyVariantInterface $familyVariants1,
        FamilyVariantInterface $familyVariants2,
        ConstraintViolationList $constraintViolationList
    ) {
        $family->getFamilyVariants()->willReturn($familyVariants);

        $familyVariants->getIterator()->willReturn($familyVariantsIterator);
        $familyVariantsIterator->current()->willReturn($familyVariants1, $familyVariants2);
        $familyVariantsIterator->valid()->willReturn(true, true, false);
        $familyVariantsIterator->rewind()->shouldBeCalled();
        $familyVariantsIterator->next()->shouldBeCalled();

        $validator->validate($familyVariants1)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);
        $validator->validate($familyVariants2)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);

        $familyVariantSaver->saveAll([$familyVariants1, $familyVariants2])->shouldBeCalled();
        $objectDetacher->detachAll([$familyVariants1, $familyVariants2])->shouldBeCalled();

        $event->getSubject()->willReturn($family);
        $this->validateAndSaveFamilyVariants($event);
    }

    function it_throws_an_exception_when_family_variants_are_invalid_and_saves_the_valid_ones(
        $validator,
        $familyVariantSaver,
        GenericEvent $event,
        FamilyInterface $family,
        Collection $familyVariants,
        \ArrayIterator $familyVariantsIterator,
        FamilyVariantInterface $familyVariants1,
        FamilyVariantInterface $familyVariants2,
        FamilyVariantInterface $familyVariants3
    ) {
        $family->getFamilyVariants()->willReturn($familyVariants);

        $familyVariants->getIterator()->willReturn($familyVariantsIterator);
        $familyVariantsIterator->current()->willReturn($familyVariants1, $familyVariants2, $familyVariants3);
        $familyVariantsIterator->valid()->willReturn(true, true, true, false);
        $familyVariantsIterator->rewind()->shouldBeCalled();
        $familyVariantsIterator->next()->shouldBeCalled();

        $familyVariants1->getCode()->willReturn('family_variant_1');
        $familyVariants2->getCode()->willReturn('family_variant_2');

        $constraintViolation1 = new ConstraintViolation('Error 1 with family variant', '', [], '', '', '10,45');
        $constraintViolation2 = new ConstraintViolation('Error 2 with family variant', '', [], '', '', '10,45');

        $constraintViolationList1 = new ConstraintViolationList([$constraintViolation1, $constraintViolation2]);
        $constraintViolationList2 = new ConstraintViolationList([$constraintViolation1]);
        $constraintViolationList3 = new ConstraintViolationList();

        $validator->validate($familyVariants1)->willReturn($constraintViolationList1);
        $validator->validate($familyVariants2)->willReturn($constraintViolationList2);
        $validator->validate($familyVariants3)->willReturn($constraintViolationList3);

        $familyVariantSaver->saveAll([$familyVariants3])->shouldBeCalled();

        $event->getSubject()->willReturn($family);
        $errorMessage = 'One or more errors occured while updating the following family variants:\n' .
            'family_variant_1:\n- Error 1 with family variant\n- Error 2 with family variant\n' .
            'family_variant_2:\n- Error 1 with family variant\n';
        $this->shouldThrow(new \LogicException($errorMessage))->during('validateAndSaveFamilyVariants', [$event]);
    }
}
