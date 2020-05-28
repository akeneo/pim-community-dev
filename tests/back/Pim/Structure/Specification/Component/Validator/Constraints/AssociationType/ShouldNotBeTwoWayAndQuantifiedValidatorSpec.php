<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints\AssociationType;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AssociationType\ShouldNotBeTwoWayAndQuantified;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
        $associationType->isTwoWay()->shouldReturn(false);
        $associationType->isQuantified()->shouldReturn(true);

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($associationType, $constraint);
    }

    public function it_builds_violation_when_association_type_when_is_two_way_and_quantified(
        AssociationType $associationType,
        ShouldNotBeTwoWayAndQuantified $constraint,
        ExecutionContextInterface $context
    ) {
        $associationType->isTwoWay()->shouldReturn(true);
        $associationType->isQuantified()->shouldReturn(true);

        $context
            ->addViolation('Association type can be either quantified or two-way')
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
