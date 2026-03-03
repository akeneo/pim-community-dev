<?php

namespace spec\Akeneo\Tool\Component\Localization\Validator\Constraints;

use Akeneo\Tool\Component\Localization\Factory\DateFactory;
use Akeneo\Tool\Component\Localization\Validator\Constraints\DateFormat;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DateFormatValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->beConstructedWith(new DateFactory([]));
        $this->initialize($context);
    }

    function it_does_not_add_violation_null_value(
        $context,
        DateFormat $constraint,
    ) {
        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_when_format_is_respected(
        $context,
        DateFormat $constraint,
    ) {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';
        $this->validate('21/12/2015', $constraint)->shouldReturn(null);

        $format = 'yyyy-MM-dd';
        $constraint->dateFormat = $format;
        $this->validate('2015-12-21', $constraint)->shouldReturn(null);

        $date = 'Tuesday 31 December 2015';
        $format = 'EEEE dd MMMM yyyy';
        $constraint->dateFormat = $format;
        $this->validate($date, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_when_validating_format_is_not_respected(
        $context,
        DateFormat $constraint,
        ConstraintViolationBuilderInterface $violation,
    ) {
        $date = '2015-12-21';
        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';

        $context
            ->buildViolation($constraint->message, ['{{ date_format }}' => $format])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($date, $constraint);
    }

    function it_adds_violation_when_separators_are_not_respected(
        $context,
        DateFormat $constraint,
        ConstraintViolationBuilderInterface $violation,
    ) {
        $date = '21-12-2015';
        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';

        $context
            ->buildViolation($constraint->message, ['{{ date_format }}' => $format])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($date, $constraint);
    }

    function it_adds_violation_when_separators_with_letter_are_not_respected(
        $context,
        DateFormat $constraint,
        ConstraintViolationBuilderInterface $violation,
    ) {
        $date = 'Tuesday,31 December 2015';
        $format = 'EEEE dd MMMM yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';

        $context
            ->buildViolation($constraint->message, ['{{ date_format }}' => $format])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($date, $constraint);
    }
}
