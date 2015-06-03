<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ValidNumberRange;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ExecutionContextInterface;

class ValidNumberRangeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\ValidNumberRangeValidator');
    }

    function it_does_nothing_when_number_range_is_valid(
        $context,
        AttributeInterface $attribute,
        Constraint $constraint
    ) {
        $attribute->getNumberMin()->willReturn(1);
        $attribute->getNumberMax()->willReturn(9);

        $attribute->isNegativeAllowed()->willReturn(true);

        $context
            ->addViolationAt(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_min_is_negative_allowed_but_decimal_not_allowed(
        $context,
        AttributeInterface $attribute,
        ValidNumberRange $constraint
    ) {
        $attribute->isNegativeAllowed()->willReturn(true);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $attribute->getNumberMin()->willReturn(-1.2);
        $attribute->getNumberMax()->willReturn(1);

        $context
            ->addViolationAt('numberMin', $constraint->invalidNumberMessage)
            ->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_max_is_negative_allowed_but_decimal_not_allowed(
        $context,
        AttributeInterface $attribute,
        ValidNumberRange $constraint
    ) {
        $attribute->isNegativeAllowed()->willReturn(true);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $attribute->getNumberMin()->willReturn(-2);
        $attribute->getNumberMax()->willReturn(-1.2);

        $context
            ->addViolationAt('numberMax', $constraint->invalidNumberMessage)
            ->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_min_is_not_negative_allowed(
        $context,
        AttributeInterface $attribute,
        ValidNumberRange $constraint
    ) {
        $attribute->isNegativeAllowed()->willReturn(false);

        $attribute->getNumberMin()->willReturn(-1);
        $attribute->getNumberMax()->willReturn(1);

        $context
            ->addViolationAt('numberMin', $constraint->invalidNumberMessage)
            ->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_min_is_greater_than_max(
        $context,
        AttributeInterface $attribute,
        ValidNumberRange $constraint
    ) {
        $attribute->getNumberMin()->willReturn(9);
        $attribute->getNumberMax()->willReturn(1);

        $attribute->isNegativeAllowed()->willReturn(false);

        $context
            ->addViolationAt('numberMax', $constraint->message)
            ->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }
}
