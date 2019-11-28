<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\RangeValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
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
        $this->shouldHaveType(RangeValidator::class);
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
            ->buildViolation($constraint->notInRangeMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', 150)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ min }}', 0)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ max }}', 100)->shouldBeCalled()->willReturn($violation);

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
            ->buildViolation($constraint->notInRangeMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', 9.99999)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ min }}', 10)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ max }}', 100)->shouldBeCalled()->willReturn($violation);

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
            ->buildViolation($constraint->notInRangeMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ min }}', 10)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ max }}', 100)->shouldBeCalled()->willReturn($violation);
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
            ->buildViolation($constraint->notInRangeMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ min }}', 10)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ max }}', 100)->shouldBeCalled()->willReturn($violation);
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
            ->buildViolation($constraint->notInRangeMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ min }}', 10)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ max }}', 100)->shouldBeCalled()->willReturn($violation);
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

    function it_validates_nullable_value($context, Range $constraint)
    {
        $constraint->min = 10;
        $constraint->max = 20;

        $context
            ->buildViolation('myMessage', ['{{ value }}' => 21, '{{ limit }}' => 20])
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    /**
     * This allows to have a proper message "This value should be between 1 and 9.22E18.", but only if the value have no maximum value but is superior to integer php limit (as we can't store it).
     * If it's inferior to php limit value, the desired message is just "This value should be 1 or more." (for example).
     */
    function it_forces_max_values_at_php_integer_limit_when_value_is_not_bound_by_a_maximum_value_but_is_superior_to_integer_php_limit(
        ExecutionContextInterface $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 10;

        $context
            ->buildViolation($constraint->notInRangeMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ min }}', 10)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ max }}', PHP_INT_MAX)->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate('6666666666666666666666666666666666666', $constraint);
    }

    function it_forces_max_values_at_integer_php_limit_when_the_maximum_limit_is_superior_to_integer_php_limit(
        ExecutionContextInterface $context,
        Range $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->min = 10;
        $constraint->max = '6666666666666666666666666666666666666';

        $context
            ->buildViolation($constraint->notInRangeMessage)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter('{{ value }}', Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ min }}', 10)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('{{ max }}', PHP_INT_MAX)->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate('6666666666666666666666666666666666666', $constraint);
    }
}
