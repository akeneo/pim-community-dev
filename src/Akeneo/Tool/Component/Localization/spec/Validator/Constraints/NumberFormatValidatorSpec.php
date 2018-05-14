<?php

namespace spec\Akeneo\Tool\Component\Localization\Validator\Constraints;

use Akeneo\Tool\Component\Localization\Validator\Constraints\NumberFormat;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NumberFormatValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->beConstructedWith(['.' => 'dot (.)']);
        $this->initialize($context);
    }

    function it_does_not_add_violation_null_value($context, NumberFormat $constraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_does_not_add_violation_when_format_is_respected($context, NumberFormat $constraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $constraint->decimalSeparator = ',';
        $this->validate('12,45', $constraint);

        $constraint->decimalSeparator = '.';
        $this->validate('12.45', $constraint);
        $this->validate('12', $constraint);
        $this->validate('0', $constraint);
        $this->validate(0, $constraint);
    }

    function it_adds_violation_when_format_is_not_respected(
        $context,
        NumberFormat $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $decimalSeparator = '.';
        $constraint->decimalSeparator = $decimalSeparator;

        $context
            ->buildViolation(
                'This type of value expects the use of dot (.) to separate decimals.',
                ['{{ decimal_separator }}' => '.']
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate('12,45', $constraint);
    }
}
