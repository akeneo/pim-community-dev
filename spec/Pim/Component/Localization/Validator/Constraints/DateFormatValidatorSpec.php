<?php

namespace spec\Pim\Component\Localization\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Validator\Constraints\DateFormat;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DateFormatValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_does_not_add_violation_null_value($context, DateFormat $constraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_when_format_is_respected($context, DateFormat $constraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $constraint->dateFormat = 'd/m/Y';
        $this->validate('21/12/2015', $constraint)->shouldReturn(null);

        $constraint->dateFormat = 'Y-m-d';
        $this->validate('2015-12-21', $constraint)->shouldReturn(null);
    }

    function it_adds_violation_when_validating_format_is_not_respected(
        $context,
        DateFormat $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $format = 'd/m/Y';
        $constraint->dateFormat = $format;

        $context
            ->buildViolation($constraint->message, ['{{ date_format }}' => $format])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate('2015-12-21', $constraint);
    }
}
