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
    function let(ExecutionContextInterface $context, DateFactory $dateFactory)
    {
        $this->beConstructedWith($dateFactory);
        $this->initialize($context);
    }

    function it_does_not_add_violation_null_value(
        $context,
        $dateFactory,
        DateFormat $constraint,
        \IntlDateFormatter $dateFormatter
    ) {
        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $dateFactory->create(['date_format' => $format])->willReturn($dateFormatter);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_when_format_is_respected(
        $context,
        $dateFactory,
        DateFormat $constraint,
        \IntlDateFormatter $dateFormatter
    ) {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $format = 'dd/MM/yyyy';
        $dateFactory->create(['date_format' => $format])->willReturn($dateFormatter);
        $constraint->dateFormat = $format;
        $this->validate('21/12/2015', $constraint)->shouldReturn(null);

        $format = 'yyyy-MM-dd';
        $dateFactory->create(['date_format' => $format])->willReturn($dateFormatter);
        $constraint->dateFormat = $format;
        $this->validate('2015-12-21', $constraint)->shouldReturn(null);

        $date = 'Tuesday 31 December 2015';
        $format = 'EEEE dd MMMM yyyy';
        $dateFactory->create(['date_format' => $format])->willReturn($dateFormatter);
        $constraint->dateFormat = $format;
        $this->validate($date, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_when_validating_format_is_not_respected(
        $context,
        $dateFactory,
        DateFormat $constraint,
        ConstraintViolationBuilderInterface $violation,
        \IntlDateFormatter $dateFormatter
    ) {
        $date = '2015-12-21';
        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $dateFactory->create(['date_format' => $format])->willReturn($dateFormatter);
        $dateFormatter->parse($date)->willReturn(false);
        $dateFormatter->setLenient(false)->shouldBeCalled();

        $context
            ->buildViolation($constraint->message, ['{{ date_format }}' => $format])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($date, $constraint);
    }

    function it_adds_violation_when_separators_are_not_respected(
        $context,
        $dateFactory,
        DateFormat $constraint,
        ConstraintViolationBuilderInterface $violation,
        \IntlDateFormatter $dateFormatter
    ) {
        $date = '21-12-2015';
        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $dateFactory->create(['date_format' => $format])->willReturn($dateFormatter);
        $dateFormatter->parse($date)->willReturn(123456);
        $dateFormatter->setLenient(false)->shouldBeCalled();

        $context
            ->buildViolation($constraint->message, ['{{ date_format }}' => $format])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($date, $constraint);
    }

    function it_adds_violation_when_separators_with_letter_are_not_respected(
        $context,
        $dateFactory,
        DateFormat $constraint,
        ConstraintViolationBuilderInterface $violation,
        \IntlDateFormatter $dateFormatter
    ) {
        $date = 'Tuesday,31 December 2015';
        $format = 'EEEE dd MMMM yyyy';
        $constraint->dateFormat = $format;
        $dateFactory->create(['date_format' => $format])->willReturn($dateFormatter);
        $dateFormatter->parse($date)->willReturn(123456);
        $dateFormatter->setLenient(false)->shouldBeCalled();

        $context
            ->buildViolation($constraint->message, ['{{ date_format }}' => $format])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($date, $constraint);
    }
}
