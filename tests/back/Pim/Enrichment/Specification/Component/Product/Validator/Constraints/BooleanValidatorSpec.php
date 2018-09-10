<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\BooleanValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Boolean;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class BooleanValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BooleanValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_does_not_add_violation_null_value($context, Boolean $constraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_does_not_add_violation_when_validates_boolean_value($context, Boolean $constraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(true, $constraint);
        $this->validate(false, $constraint);
    }

    function it_does_not_add_violation_when_validates_boolean_like_value($context, Boolean $constraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(1, $constraint);
        $this->validate(0, $constraint);
        $this->validate('1', $constraint);
        $this->validate('0', $constraint);
    }

    function it_adds_violation_when_validating_non_boolean_value(
        $context,
        Boolean $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context
            ->buildViolation(
                $constraint->message,
                ['%attribute%' => '', '%givenType%' => 'integer']
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(666, $constraint);

        $context
            ->buildViolation(
                $constraint->message,
                ['%attribute%' => '', '%givenType%' => 'string']
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate('foo', $constraint);
        $this->validate('true', $constraint);

        $context
            ->buildViolation(
                $constraint->message,
                ['%attribute%' => '', '%givenType%' => 'array']
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(['foo'], $constraint);
        $this->validate([true], $constraint);
    }

    function it_does_not_add_violation_when_validates_boolean_product_value(
        $context,
        Boolean $constraint,
        ValueInterface $value
    ) {
        $value->getAttributeCode()->willReturn('foo');
        $value->getData()->willReturn(true);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_adds_violation_when_validates_null_product_value(
        $context,
        Boolean $constraint,
        ValueInterface $value
    ) {
        $value->getAttributeCode()->willReturn('foo');
        $value->getData()->willReturn(null);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_adds_violation_when_validates_non_boolean_product_value(
        $context,
        Boolean $constraint,
        ValueInterface $value,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->getAttributeCode()->willReturn('foo');
        $value->getData()->willReturn(666);

        $context
            ->buildViolation(
                $constraint->message,
                ['%attribute%' => 'foo', '%givenType%' => 'integer']
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($value, $constraint);
    }
}
