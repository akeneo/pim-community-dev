<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ValidDateRange;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ExecutionContextInterface;

class ValidDateRangeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\ValidDateRangeValidator');
    }

    function it_does_nothing_if_dates_and_date_range_are_valid(
        $context,
        AttributeInterface $attribute,
        Constraint $constraint
    ) {
        $date1 = new \DateTime();
        $date1->setDate(2012,12,21);

        $date2 = new \DateTime();

        $attribute->getDateMin()->willReturn($date1);
        $attribute->getDateMax()->willReturn($date2);

        $context
            ->addViolationAt(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_when_dates_are_valid_but_date_max_is_before_date_min(
        $context,
        AttributeInterface $attribute,
        ValidDateRange $constraint
    ) {
        $date1 = new \DateTime();
        $date1->setDate(2020,12,21);

        $date2 = new \DateTime();

        $attribute->getDateMin()->willReturn($date1);
        $attribute->getDateMax()->willReturn($date2);

        $context
            ->addViolationAt('dateMax', $constraint->message)
            ->shouldBeCalled();

        $this->validate($attribute,$constraint);
    }

    function it_adds_violation_when_date_max_is_not_valid(
        $context,
        AttributeInterface $attribute,
        ValidDateRange $constraint
    ) {
        $date = new \DateTime();

        $attribute->getDateMin()->willReturn($date);
        $attribute->getDateMax()->willReturn('not_a_date');

        $context
            ->addViolationAt('dateMax', $constraint->invalidDateMessage)
            ->shouldBeCalled();

        $this->validate($attribute,$constraint);
    }

    function it_adds_violation_when_date_min_is_not_valid(
        $context,
        AttributeInterface $attribute,
        ValidDateRange $constraint
    ) {
        $date = new \DateTime();

        $attribute->getDateMin()->willReturn('not_a_date');
        $attribute->getDateMax()->willReturn($date);

        $context
            ->addViolationAt('dateMin', $constraint->invalidDateMessage)
            ->shouldBeCalled();

        $this->validate($attribute,$constraint);
    }
}
