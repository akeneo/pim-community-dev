<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ImmutableVariantAxes;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ImmutableVariantAxesValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ImmutableVariantAxesValues;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ImmutableVariantAxesValidatorSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($entityManager);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImmutableVariantAxesValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    function it_throws_an_exception_if_it_does_not_validate_a_variant_attribute_set(
        \stdClass $entity,
        ImmutableVariantAxesValues $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            $entity,
            $constraint
        ]);
    }

    function it_throws_an_exception_if_it_does_not_validate_against_the_correct_constraint(
        VariantAttributeSet $entity,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            $entity,
            $constraint
        ]);
    }

    function it_does_not_build_a_violation_if_the_original_entity_is_not_found(
        $entityManager,
        $context,
        VariantAttributeSet $entity,
        ImmutableVariantAxes $constraint,
        UnitOfWork $uow
    ) {
        $entityManager->getUnitOfWork()->willReturn($uow);
        $uow->getOriginalEntityData($entity)->willReturn([]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_builds_a_violation_if_an_axis_has_been_removed(
        $entityManager,
        $context,
        VariantAttributeSet $entity,
        ImmutableVariantAxes $constraint,
        UnitOfWork $uow,
        ConstraintViolationBuilderInterface $violationBuilder,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3
    ) {
        $attribute1->getCode()->willReturn('axis_1');
        $attribute2->getCode()->willReturn('axis_2');
        $attribute3->getCode()->willReturn('axis_1');

        $constraint->propertyPath = 'axes';
        $entityManager->getUnitOfWork()->willReturn($uow);
        $uow->getOriginalEntityData($entity)->willReturn([
            'axes' => new ArrayCollection([
                $attribute1->getWrappedObject(),
                $attribute2->getWrappedObject()
            ])
        ]);

        $entity->getAxes()->willReturn(new ArrayCollection([$attribute3->getWrappedObject()]));
        $entity->getLevel()->willReturn(1);

        $context
            ->buildViolation(ImmutableVariantAxes::IMMUTABLE_VARIANT_AXES, ['%level%' => 1])
            ->willReturn($violationBuilder);

        $violationBuilder->atPath('axes')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_builds_a_violation_if_an_axis_has_been_added(
        $entityManager,
        $context,
        VariantAttributeSet $entity,
        ImmutableVariantAxes $constraint,
        UnitOfWork $uow,
        ConstraintViolationBuilderInterface $violationBuilder,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3
    ) {
        $attribute1->getCode()->willReturn('axis_1');
        $attribute2->getCode()->willReturn('axis_2');
        $attribute3->getCode()->willReturn('axis_1');

        $constraint->propertyPath = 'axes';
        $entityManager->getUnitOfWork()->willReturn($uow);
        $uow->getOriginalEntityData($entity)->willReturn([
            'axes' => new ArrayCollection([$attribute1->getWrappedObject()])
        ]);

        $entity->getAxes()->willReturn(new ArrayCollection([
            $attribute2->getWrappedObject(),
            $attribute3->getWrappedObject(),
        ]));
        $entity->getLevel()->willReturn(1);

        $context
            ->buildViolation(ImmutableVariantAxes::IMMUTABLE_VARIANT_AXES, ['%level%' => 1])
            ->willReturn($violationBuilder);

        $violationBuilder->atPath('axes')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }
}
