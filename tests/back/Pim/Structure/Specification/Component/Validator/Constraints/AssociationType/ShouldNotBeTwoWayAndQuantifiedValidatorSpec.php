<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints\AssociationType;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AssociationType\ShouldNotBeTwoWayAndQuantified;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ShouldNotBeTwoWayAndQuantifiedValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    public function it_validate_that_association_type_is_not_two_way_and_quantified(
        ShouldNotBeTwoWayAndQuantified $constraint,
        ExecutionContextInterface $context,
        AssociationType $associationType
    ) {
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(true);

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($associationType, $constraint);
    }

    public function it_builds_violation_when_association_type_when_is_two_way_and_quantified(
        AssociationType $associationType,
        ShouldNotBeTwoWayAndQuantified $constraint,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $associationType->isTwoWay()->willReturn(true);
        $associationType->isQuantified()->willReturn(true);

        $context
            ->buildViolation('pim_structure.validation.association_type.cannot_be_quantified_and_two_way')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder
            ->addViolation()
            ->shouldBeCalled();

        $this->validate($associationType, $constraint);
    }

    function it_only_support_should_not_be_two_way_and_quantified_constraint(
        Constraint $otherConstraint,
        AssociationType $associationType
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$associationType, $otherConstraint]);
    }

    function it_only_works_with_association_type_object(
        ShouldNotBeTwoWayAndQuantified $constraint,
        ProductInterface $product
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }
}
