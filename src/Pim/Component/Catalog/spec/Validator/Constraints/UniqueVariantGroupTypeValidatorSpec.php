<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Pim\Component\Catalog\Validator\Constraints\UniqueVariantGroupType;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueVariantGroupTypeValidatorSpec extends ObjectBehavior
{
    function let(GroupTypeRepositoryInterface $repository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($repository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\UniqueVariantGroupTypeValidator');
    }

    function it_does_nothing_if_group_type_is_not_variant(
        $context,
        GroupTypeInterface $groupType,
        Constraint $constraint
    ) {
        $groupType->isVariant()->willReturn(false);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($groupType, $constraint);
    }

    function it_does_nothing_if_group_type_ids_are_the_same(
        $context,
        $repository,
        GroupTypeInterface $groupType,
        GroupTypeInterface $variantGroupType,
        Constraint $constraint
    ) {
        $groupType->isVariant()->willReturn(true);
        $groupType->getId()->willReturn(1);

        $repository->getVariantGroupType()->willReturn($variantGroupType);

        $variantGroupType->getId()->willReturn(1);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($groupType, $constraint);
    }

    function it_adds_a_violation_if_a_variant_group_type_already_exists(
        $context,
        $repository,
        GroupTypeInterface $groupType,
        GroupTypeInterface $variantGroupType,
        UniqueVariantGroupType $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $groupType->isVariant()->willReturn(true);
        $groupType->getId()->willReturn(2);

        $repository->getVariantGroupType()->willReturn($variantGroupType);

        $variantGroupType->getId()->willReturn(1);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->addViolation()->shouldBeCalled();

        $this->validate($groupType, $constraint);
    }
}
