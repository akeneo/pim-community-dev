<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidNumberRange;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidNumberRangeValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidNumberRangeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValidNumberRangeValidator::class);
    }

    function it_does_nothing_when_number_range_is_valid(
        $context,
        AttributeInterface $attribute,
        ValidNumberRange $constraint
    ) {
        $attribute->getNumberMin()->willReturn(1);
        $attribute->getNumberMax()->willReturn(9);

        $attribute->isNegativeAllowed()->willReturn(true);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_max_reaches_the_php_int_max(
        $context,
        ValidNumberRange $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $number = new Attribute();
        $number->setNumberMax('555337203685477580742332342');

        $context
            ->buildViolation(
                ValidNumberRange::PHP_INT_MAX_REACHED,
                ['%php_int_max%' => PHP_INT_MAX]
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('numberMax')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($number, $constraint);
    }

    function it_adds_violation_when_min_is_negative_allowed_but_decimal_not_allowed(
        $context,
        AttributeInterface $attribute,
        ValidNumberRange $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute->isNegativeAllowed()->willReturn(true);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $attribute->getNumberMin()->willReturn(-1.2);
        $attribute->getNumberMax()->willReturn(1);

        $context
            ->buildViolation($constraint->invalidNumberMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('numberMin')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_max_is_negative_allowed_but_decimal_not_allowed(
        $context,
        AttributeInterface $attribute,
        ValidNumberRange $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute->isNegativeAllowed()->willReturn(true);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $attribute->getNumberMin()->willReturn(-2);
        $attribute->getNumberMax()->willReturn(-1.2);

        $context
            ->buildViolation($constraint->invalidNumberMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('numberMax')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_min_is_not_negative_allowed(
        $context,
        AttributeInterface $attribute,
        ValidNumberRange $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute->isNegativeAllowed()->willReturn(false);

        $attribute->getNumberMin()->willReturn(-1);
        $attribute->getNumberMax()->willReturn(1);

        $context
            ->buildViolation($constraint->invalidNumberMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('numberMin')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_min_is_greater_than_max(
        $context,
        AttributeInterface $attribute,
        ValidNumberRange $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute->getNumberMin()->willReturn(9);
        $attribute->getNumberMax()->willReturn(1);

        $attribute->isNegativeAllowed()->willReturn(false);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('numberMax')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }
}
