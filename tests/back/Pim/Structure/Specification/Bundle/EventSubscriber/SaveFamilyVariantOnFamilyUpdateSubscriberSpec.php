<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\ComputeFamilyVariantStructureChangesSubscriber;
use Akeneo\Pim\Structure\Bundle\EventSubscriber\SaveFamilyVariantOnFamilyUpdateSubscriber;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SaveFamilyVariantOnFamilyUpdateSubscriberSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        BulkSaverInterface $bulkFamilyVariantSaver,
        BulkObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith($validator, $bulkFamilyVariantSaver, $objectDetacher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SaveFamilyVariantOnFamilyUpdateSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE => 'onUnitarySave',
            StorageEvents::POST_SAVE_ALL => 'onBulkSave',
        ]);
    }

    function it_only_supports_families_object(
        $validator,
        BulkSaverInterface $bulkFamilyVariantSaver,
        GenericEvent $event,
        \stdClass $object
    ) {
        $validator->validate()->shouldNotBeCalled();
        $bulkFamilyVariantSaver->saveAll(Argument::cetera())->shouldNotBeCalled();

        $event->getSubject()->willReturn($object);
        $this->onUnitarySave($event);
    }

    function it_validates_and_saves_family_variants_on_family_update_on_unitary_save(
        $validator,
        BulkSaverInterface $bulkFamilyVariantSaver,
        GenericEvent $event,
        ConstraintViolationList $constraintViolationList
    ) {
        $familyVariants1 = new FamilyVariant();
        $familyVariants2 = new FamilyVariant();
        $family = new Family();
        $family->setFamilyVariants(new ArrayCollection([$familyVariants1, $familyVariants2]));

        $validator->validate($familyVariants1)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);
        $validator->validate($familyVariants2)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);

        $bulkFamilyVariantSaver->saveAll(
            [$familyVariants1, $familyVariants2],
            [ComputeFamilyVariantStructureChangesSubscriber::DISABLE_JOB_LAUNCHING => false]
        )->shouldBeCalled();

        $event->getSubject()->willReturn($family);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);
        $this->onUnitarySave($event);
    }

    function it_throws_an_exception_when_family_variants_are_invalid_and_saves_the_valid_ones_on_unitary_save(
        $validator,
        BulkSaverInterface $bulkFamilyVariantSaver,
        GenericEvent $event,
    ) {
        $familyVariants1 = new FamilyVariant();
        $familyVariants1->setCode('family_variant_1');
        $familyVariants2 = new FamilyVariant();
        $familyVariants2->setCode('family_variant_2');
        $familyVariants3 = new FamilyVariant();
        $family = new Family();
        $family->setFamilyVariants(new ArrayCollection([$familyVariants1, $familyVariants2, $familyVariants3]));

        $constraintViolation1 = new ConstraintViolation('Error 1 with family variant', '', [], '', '', '10,45');
        $constraintViolation2 = new ConstraintViolation('Error 2 with family variant', '', [], '', '', '10,45');

        $constraintViolationList1 = new ConstraintViolationList([$constraintViolation1, $constraintViolation2]);
        $constraintViolationList2 = new ConstraintViolationList([$constraintViolation1]);
        $constraintViolationList3 = new ConstraintViolationList();

        $validator->validate($familyVariants1)->willReturn($constraintViolationList1);
        $validator->validate($familyVariants2)->willReturn($constraintViolationList2);
        $validator->validate($familyVariants3)->willReturn($constraintViolationList3);

        $bulkFamilyVariantSaver->saveAll(
            [$familyVariants3],
            [ComputeFamilyVariantStructureChangesSubscriber::DISABLE_JOB_LAUNCHING => false]
        )->shouldBeCalled();

        $event->getSubject()->willReturn($family);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);
        $errorMessage = 'One or more errors occurred while updating the following family variants:\n' .
            'family_variant_1:\n- Error 1 with family variant\n- Error 2 with family variant\n' .
            'family_variant_2:\n- Error 1 with family variant\n';
        $this->shouldThrow(new \LogicException($errorMessage))->during('onUnitarySave', [$event]);
    }

    function it_does_not_save_if_on_non_unitary_save_and_POST_SAVE(
        BulkSaverInterface $bulkFamilyVariantSaver,
        GenericEvent $event,
        FamilyInterface $family
    ) {
        $bulkFamilyVariantSaver->saveAll(Argument::cetera())->shouldNotBeCalled();

        $event->getSubject()->willReturn($family);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $this->onUnitarySave($event);
    }

    function it_validates_and_saves_family_variants_on_family_update_on_bulk_save(
        $validator,
        BulkSaverInterface $bulkFamilyVariantSaver,
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

        $bulkFamilyVariantSaver->saveAll(
            [$familyVariants1, $familyVariants2],
            [ComputeFamilyVariantStructureChangesSubscriber::DISABLE_JOB_LAUNCHING => true]
        )->shouldBeCalled();

        $event->getSubject()->willReturn([$family]);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);
        $this->onBulkSave($event);
    }

    function it_throws_an_exception_when_family_variants_are_invalid_and_saves_the_valid_ones_on_bulk_save(
        $validator,
        BulkSaverInterface $bulkFamilyVariantSaver,
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

        $bulkFamilyVariantSaver->saveAll(
            [$familyVariants3],
            [ComputeFamilyVariantStructureChangesSubscriber::DISABLE_JOB_LAUNCHING => true]
        )->shouldBeCalled();

        $event->getSubject()->willReturn([$family]);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);
        $errorMessage = 'One or more errors occurred while updating the following family variants:\n' .
            'family_variant_1:\n- Error 1 with family variant\n- Error 2 with family variant\n' .
            'family_variant_2:\n- Error 1 with family variant\n';
        $this->shouldThrow(new \LogicException($errorMessage))->during('onBulkSave', [$event]);
    }

    function it_does_not_bulk_save_if_on_non_unitary_save_and_POST_SAVE_ALL_event(
        $bulkFamilyVariantSaver,
        GenericEvent $event,
        FamilyInterface $family
    ) {
        $bulkFamilyVariantSaver->saveAll()->shouldNotBeCalled();

        $event->getSubject()->willReturn($family);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $this->onBulkSave($event);
    }

    function it_does_not_force_launch_compute_family_variant_job_when_attributes_did_not_change(
        ValidatorInterface $validator,
        BulkSaverInterface $bulkFamilyVariantSaver,
        GenericEvent $event,
        ConstraintViolationList $constraintViolationList,
    ) {
        $familyVariants1 = new FamilyVariant();
        $familyVariants2 = new FamilyVariant();
        $family = new Family();
        $family->setFamilyVariants(new ArrayCollection([$familyVariants1, $familyVariants2]));

        $event->getSubject()->willReturn($family);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $validator->validate($familyVariants1)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);
        $validator->validate($familyVariants2)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);
        $bulkFamilyVariantSaver->saveAll(
            [$familyVariants1, $familyVariants2],
            [ComputeFamilyVariantStructureChangesSubscriber::DISABLE_JOB_LAUNCHING => false]
        )->shouldBeCalled();

        $this->onUnitarySave($event);
    }
}
