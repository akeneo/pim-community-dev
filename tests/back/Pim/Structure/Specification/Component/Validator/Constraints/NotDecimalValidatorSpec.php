<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\NotDecimal;
use Akeneo\Pim\Structure\Component\Validator\Constraints\NotDecimalValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotDecimalValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotDecimalValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_validates_numeric_value($context)
    {
        $constraint = new NotDecimal();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(100, $constraint);
    }

    function it_does_not_validate_decimal_value(
        $context,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint = new NotDecimal();
        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(100.5, $constraint);
    }

    function it_validates_string_value($context)
    {
        $constraint = new NotDecimal();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('100', $constraint);
    }

    function it_does_not_validate_string_value(
        $context,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint = new NotDecimal();
        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate('100.5', $constraint);
    }

    function it_validates_nullable_value($context, NotDecimal $constraint)
    {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }
}
