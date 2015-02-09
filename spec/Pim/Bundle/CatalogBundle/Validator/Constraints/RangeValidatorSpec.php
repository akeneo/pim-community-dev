<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Range;
use Pim\Bundle\CatalogBundle\Validator\Constraints\RangeValidator;
use Symfony\Component\Validator\ExecutionContextInterface;
use Prophecy\Argument;

class RangeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_valid_range(
        $context,
        RangeValidator $rangeValidator,
        Range $constraint)
    {
        $constraint->min = 0;
        $constraint->max = 50;

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->validate(25, $constraint);
    }

    function it_not_valid_range(
        $context,
        RangeValidator $rangeValidator,
        Range $constraint)
    {
        $constraint->min = 0;
        $constraint->max = 100;

        $context
            ->addViolation($constraint->maxMessage, ['{{ value }}' => 150, '{{ limit }}' => 100])
            ->shouldBeCalled()
        ;

        $this->validate(150, $constraint);
    }

    function it_valid_date_range(
        $context,
        RangeValidator $rangeValidator,
        Range $constraint)
    {
        # with min & max
        $constraint->min = new \DateTime('2013-06-13');
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->validate(new \DateTime('2013-12-25'), $constraint);

        # without max
        $constraint->max = null;

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->validate(new \DateTime('2013-12-25'), $constraint);

        # without min
        $constraint->min = null;
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->validate(new \DateTime('2013-12-25'), $constraint);
    }

    function it_not_valid_date_range(
        $context,
        RangeValidator $rangeValidator,
        Range $constraint)
    {
        # with min & max
        $constraint->min = new \DateTime('2013-06-13');
        $constraint->max = new \DateTime('2014-06-13');

        $value = new \DateTime('2012-12-25');
        $context
            ->addViolation($constraint->minDateMessage, ['{{ limit }}' => '2013-06-13'])
            ->shouldBeCalled()
        ;

        $this->validate($value, $constraint);

        # without max
        $constraint->max = null;
        $value = new \DateTime('2012-12-25');
        $context
            ->addViolation($constraint->minDateMessage, ['{{ limit }}' => '2013-06-13'])
            ->shouldBeCalled()
        ;
        $this->validate($value, $constraint);

        # without min
        $constraint->min = null;
        $constraint->max = new \DateTime('2014-06-13');
        $value = new \DateTime('2015-12-25');
        $context
            ->addViolation($constraint->maxDateMessage, ['{{ limit }}' => '2014-06-13'])
            ->shouldBeCalled()
        ;
        $this->validate($value, $constraint);
    }
}
