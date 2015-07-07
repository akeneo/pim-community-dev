<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Range;
use Pim\Bundle\CatalogBundle\Validator\Constraints\RangeValidator;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Symfony\Component\Validator\ExecutionContextInterface;
use Prophecy\Argument;

class RangeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\RangeValidator');
    }

    function it_validates_a_value_in_range(
        $context,
        Range $constraint)
    {
        $constraint->min = 0;
        $constraint->max = 50;

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(25, $constraint);
    }

    function it_validates_a_value_as_integer_and_limit_min(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(10, $constraint);
    }

    function it_validates_a_value_as_string_and_limit_min(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('10', $constraint);
    }

    function it_validates_a_value_as_integer_and_limit_max(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(100, $constraint);
    }

    function it_validates_a_value_as_string_and_limit_max(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('100', $constraint);
    }

    function it_does_not_validate_a_value_not_in_range(
        $context,
        Range $constraint)
    {
        $constraint->min = 0;
        $constraint->max = 100;

        $context
            ->addViolation($constraint->maxMessage, ['{{ value }}' => 150, '{{ limit }}' => 100])
            ->shouldBeCalled();

        $this->validate(150, $constraint);
    }

    function it_does_not_validate_a_value_as_integer_limit_min(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->addViolation($constraint->minMessage, ['{{ value }}' => 9.99999, '{{ limit }}' => 10])
            ->shouldBeCalled();

        $this->validate(9.99999, $constraint);
    }

    function it_does_not_validate_a_value_as_string_limit_min(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->addViolation($constraint->minMessage, ['{{ value }}' => 9.99999, '{{ limit }}' => 10])
            ->shouldBeCalled();

        $this->validate('9.99999', $constraint);
    }

    function it_does_not_validate_a_value_as_integer_limit_max(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->addViolation($constraint->maxMessage, ['{{ value }}' => 100.00001, '{{ limit }}' => 100])
            ->shouldBeCalled();

        $this->validate(100.00001, $constraint);
    }

    function it_does_not_validate_a_value_as_string_limit_max(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->addViolation($constraint->maxMessage, ['{{ value }}' => 100.00001, '{{ limit }}' => 100])
            ->shouldBeCalled();

        $this->validate('100.00001', $constraint);
    }

    function it_validates_a_date_in_range(
        $context,
        Range $constraint)
    {
        $constraint->min = new \DateTime('2013-06-13');
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2013-12-25'), $constraint);
    }

    function it_validates_a_date_without_max(
        $context,
        Range $constraint)
    {
        $constraint->min = new \DateTime('2013-06-13');

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2013-12-25'), $constraint);
    }

    function it_validates_a_date_limit_max(
        $context,
        Range $constraint)
    {
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2014-06-13'), $constraint);
    }

    function it_validates_a_date_without_min(
        $context,
        Range $constraint)
    {
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2013-12-25'), $constraint);
    }

    function it_validates_a_date_limit_min(
        $context,
        Range $constraint)
    {
        $constraint->min = new \DateTime('2014-06-13');

        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2014-06-13'), $constraint);
    }

    function it_does_not_validate_a_date_in_range(
        $context,
        Range $constraint)
    {
        $constraint->min = new \DateTime('2013-06-13');
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->addViolation($constraint->minDateMessage, ['{{ limit }}' => '2013-06-13'])
            ->shouldBeCalled();

        $this->validate(new \DateTime('2012-12-25'), $constraint);
    }

    function it_does_not_validate_a_date_without_max(
        $context,
        Range $constraint)
    {
        $constraint->min = new \DateTime('2013-06-13');
        $context
            ->addViolation($constraint->minDateMessage, ['{{ limit }}' => '2013-06-13'])
            ->shouldBeCalled();

        $this->validate(new \DateTime('2012-12-25'), $constraint);
    }

    function it_does_not_validate_a_date_without_min(
        $context,
        Range $constraint)
    {
        $constraint->max = new \DateTime('2014-06-13');
        $context
            ->addViolation($constraint->maxDateMessage, ['{{ limit }}' => '2014-06-13'])
            ->shouldBeCalled();

        $this->validate(new \DateTime('2015-12-25'), $constraint);
    }

    function it_validates_a_product_price(
        $context,
        Range $constraint,
        ProductPriceInterface $productPrice)
    {
        $constraint->min = 0;
        $constraint->max = 100;

        $productPrice->getData()->willReturn(50);
        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productPrice, $constraint);
    }

    function it_does_not_validate_a_product_price(
        $context,
        Range $constraint,
        ProductPriceInterface $productPrice)
    {
        $constraint->min = 0;
        $constraint->max = 100;

        $productPrice->getData()->willReturn(150);
        $context
            ->addViolationAt('data', $constraint->maxMessage, ['{{ value }}' => 150, '{{ limit }}' => 100])
            ->shouldBeCalled();

        $this->validate($productPrice, $constraint);
    }

    function it_validates_metric(
        $context,
        Range $constraint,
        MetricInterface $metric)
    {
        $constraint->min = 0;
        $constraint->max = 100;

        $metric->getData()->willReturn(50);
        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($metric, $constraint);
    }

    function it_does_not_validate_metric(
        $context,
        Range $constraint,
        MetricInterface $metric)
    {
        $constraint->min = 0;
        $constraint->max = 100;

        $metric->getData()->willReturn(150);
        $context
            ->addViolationAt('data', $constraint->maxMessage, ['{{ value }}' => 150, '{{ limit }}' => 100])
            ->shouldBeCalled();

        $this->validate($metric, $constraint);
    }

    function it_sets_specific_min_message(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 20;
        $constraint->minMessage = 'myMessage';

        $context
            ->addViolation('myMessage', ['{{ value }}' => 5, '{{ limit }}' => 10])
            ->shouldBeCalled();

        $this->validate(5, $constraint);
    }

    function it_sets_specific_max_message(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 20;
        $constraint->maxMessage = 'myMessage';

        $context
            ->addViolation('myMessage', ['{{ value }}' => 21, '{{ limit }}' => 20])
            ->shouldBeCalled();

        $this->validate(21, $constraint);
    }

    function it_validates_nullable_value(
        $context,
        Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 20;

        $context
            ->addViolation('myMessage', ['{{ value }}' => 21, '{{ limit }}' => 20])
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }
}
