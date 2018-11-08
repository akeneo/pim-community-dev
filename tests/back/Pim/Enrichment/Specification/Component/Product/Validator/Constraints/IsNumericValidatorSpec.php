<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumericValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsNumericValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsNumericValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_does_not_add_violation_null_value($context, IsNumeric $numericConstraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $numericConstraint);
    }

    function it_does_not_add_violation_metric_with_no_data($context, MetricInterface $metric, IsNumeric $numericConstraint)
    {
        $metric->getData()->willReturn(null);
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($metric, $numericConstraint);
    }

    function it_does_not_add_violation_product_price_with_no_data(
        $context,
        ProductPriceInterface $productPrice,
        IsNumeric $numericConstraint
    ) {
        $productPrice->getData()->willReturn(null);
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productPrice, $numericConstraint);
    }

    function it_does_not_add_violation_when_validates_numeric_value($context, IsNumeric $numericConstraint)
    {
        $propertyPath = null;
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(5, $numericConstraint);
    }

    function it_does_not_add_violation_when_validates_numeric_metric_value(
        $context,
        MetricInterface $metric,
        IsNumeric $numericConstraint
    ) {
        $metric->getData()->willReturn(5);
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($metric, $numericConstraint);
    }

    function it_does_not_add_violation_when_validates_numeric_product_price_value(
        $context,
        ProductPriceInterface $productPrice,
        IsNumeric $numericConstraint
    ) {
        $productPrice->getData()->willReturn(5);
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($productPrice, $numericConstraint);
    }

    function it_adds_violation_when_validating_non_numeric_value(
        $context,
        IsNumeric $numericConstraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context
            ->buildViolation($numericConstraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate('a', $numericConstraint);
    }

    function it_adds_violation_when_validating_non_numeric_metric_value(
        $context,
        MetricInterface $metric,
        IsNumeric $numericConstraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $metric->getData()->willReturn('a');
        $context
            ->buildViolation($numericConstraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($metric, $numericConstraint);
    }

    function it_adds_violation_when_validating_non_numeric_product_price_value(
        $context,
        ProductPriceInterface $productPrice,
        IsNumeric $numericConstraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $productPrice->getData()->willReturn('a');
        $context
            ->buildViolation($numericConstraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($productPrice, $numericConstraint);
    }
}
