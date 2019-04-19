<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRangeValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRange;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidDateRangeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValidDateRangeValidator::class);
    }

    function it_does_nothing_if_dates_and_date_range_are_valid(
        $context,
        AttributeInterface $attribute,
        ValidDateRange $constraint
    ) {
        $date1 = new \DateTime();
        $date1->setDate(2012, 12, 21);

        $date2 = new \DateTime();

        $attribute->getDateMin()->willReturn($date1);
        $attribute->getDateMax()->willReturn($date2);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_dates_are_valid_but_date_max_is_before_date_min(
        $context,
        AttributeInterface $attribute,
        ValidDateRange $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $date1 = new \DateTime();
        $date1->setDate(2020, 12, 21);

        $date2 = new \DateTime();

        $attribute->getDateMin()->willReturn($date1);
        $attribute->getDateMax()->willReturn($date2);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('dateMax')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_date_max_is_not_valid(
        $context,
        AttributeInterface $attribute,
        ValidDateRange $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $date = new \DateTime();

        $attribute->getDateMin()->willReturn($date);
        $attribute->getDateMax()->willReturn('not_a_date');

        $context
            ->buildViolation($constraint->invalidDateMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('dateMax')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_date_min_is_not_valid(
        $context,
        AttributeInterface $attribute,
        ValidDateRange $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $date = new \DateTime();

        $attribute->getDateMin()->willReturn('not_a_date');
        $attribute->getDateMax()->willReturn($date);

        $context
            ->buildViolation($constraint->invalidDateMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('dateMin')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }
}
