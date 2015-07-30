<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Range;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

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

    function it_validates_a_value_in_range($context, Range $constraint)
    {
        $constraint->min = 0;
        $constraint->max = 50;

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(25, $constraint);
    }

    function it_validates_a_value_as_integer_and_limit_min($context, Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(10, $constraint);
    }

    function it_validates_a_value_as_string_and_limit_min($context, Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('10', $constraint);
    }

    function it_validates_a_value_as_integer_and_limit_max($context, Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(100, $constraint);
    }

    function it_validates_a_value_as_string_and_limit_max($context, Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('100', $constraint);
    }

    function it_does_not_validate_a_value_not_in_range(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 0;
        $constraint->max = 100;

        $context
            ->buildViolation($constraint->maxMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', 150)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ limit }}', 100)->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate(150, $constraint);
    }

    function it_does_not_validate_a_value_as_integer_limit_min(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->buildViolation($constraint->minMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', 9.99999)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ limit }}', 10)->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate(9.99999, $constraint);
    }

    function it_does_not_validate_a_value_as_string_limit_min(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->buildViolation($constraint->minMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ limit }}', 10)->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate('9.99999', $constraint);
    }

    function it_does_not_validate_a_value_as_integer_limit_max(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->buildViolation($constraint->maxMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ limit }}', 100)->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate(100.00001, $constraint);
    }

    function it_does_not_validate_a_value_as_string_limit_max(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 10;
        $constraint->max = 100;

        $context
            ->buildViolation($constraint->maxMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ limit }}', 100)->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate('100.00001', $constraint);
    }

    function it_validates_a_date_in_range($context, Range $constraint)
    {
        $constraint->min = new \DateTime('2013-06-13');
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2013-12-25'), $constraint);
    }

    function it_validates_a_date_without_max($context, Range $constraint)
    {
        $constraint->min = new \DateTime('2013-06-13');

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2013-12-25'), $constraint);
    }

    function it_validates_a_date_limit_max($context, Range $constraint)
    {
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2014-06-13'), $constraint);
    }

    function it_validates_a_date_without_min($context, Range $constraint)
    {
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2013-12-25'), $constraint);
    }

    function it_validates_a_date_limit_min($context, Range $constraint)
    {
        $constraint->min = new \DateTime('2014-06-13');

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(new \DateTime('2014-06-13'), $constraint);
    }

    function it_does_not_validate_a_date_in_range(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = new \DateTime('2013-06-13');
        $constraint->max = new \DateTime('2014-06-13');

        $context
            ->buildViolation($constraint->minDateMessage, ['{{ limit }}' => '2013-06-13'])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(new \DateTime('2012-12-25'), $constraint);
    }

    function it_does_not_validate_a_date_without_max(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = new \DateTime('2013-06-13');
        $context
            ->buildViolation($constraint->minDateMessage, ['{{ limit }}' => '2013-06-13'])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(new \DateTime('2012-12-25'), $constraint);
    }

    function it_does_not_validate_a_date_without_min(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->max = new \DateTime('2014-06-13');
        $context
            ->buildViolation($constraint->maxDateMessage, ['{{ limit }}' => '2014-06-13'])
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(new \DateTime('2015-12-25'), $constraint);
    }

    function it_validates_a_product_price($context, Range $constraint, ProductPriceInterface $productPrice)
    {
        $constraint->min = 0;
        $constraint->max = 100;

        $productPrice->getData()->willReturn(50);
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productPrice, $constraint);
    }

    function it_does_not_validate_a_product_price(
        $context,
        Range $constraint,
        ProductPriceInterface $productPrice,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 0;
        $constraint->max = 100;

        $productPrice->getData()->willReturn(150);
        $context
            ->buildViolation($constraint->maxMessage, ['{{ value }}' => 150, '{{ limit }}' => 100])
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->atPath('data')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($productPrice, $constraint);
    }

    function it_validates_metric($context, Range $constraint, MetricInterface $metric)
    {
        $constraint->min = 0;
        $constraint->max = 100;

        $metric->getData()->willReturn(50);
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($metric, $constraint);
    }

    function it_does_not_validate_metric(
        $context,
        Range $constraint,
        MetricInterface $metric,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 0;
        $constraint->max = 100;

        $metric->getData()->willReturn(150);
        $context
            ->buildViolation($constraint->maxMessage, ['{{ value }}' => 150, '{{ limit }}' => 100])
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('data')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($metric, $constraint);
    }

    function it_sets_specific_min_message(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 10;
        $constraint->max = 20;
        $constraint->minMessage = 'myMessage';

        $context
            ->buildViolation('myMessage')
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', 5)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ limit }}', 10)->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate(5, $constraint);
    }

    function it_sets_specific_max_message(
        $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 10;
        $constraint->max = 20;
        $constraint->maxMessage = 'myMessage';

        $context
            ->buildViolation('myMessage')
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', 21)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ limit }}', 20)->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate(21, $constraint);
    }

    function it_validates_nullable_value($context, Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 20;

        $context
            ->buildViolation('myMessage', ['{{ value }}' => 21, '{{ limit }}' => 20])
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }
}
