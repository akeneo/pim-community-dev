<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumericValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsNumericValidatorSpec extends ObjectBehavior
{
    public function let(
        ExecutionContextInterface $context,
        ConstraintViolationListInterface $constraintViolationList,
    ): void
    {
        $this->initialize($context);
        $context
            ->getViolations()
            ->willReturn($constraintViolationList);
        $constraintViolationList
            ->count()
            ->willReturn(0);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IsNumericValidator::class);
    }

    public function it_is_a_validator_constraint(): void
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\ConstraintValidator');
    }

    public function it_does_not_add_violation_null_value(ExecutionContextInterface $context, IsNumeric $numericConstraint): void
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $numericConstraint);
    }

    public function it_does_not_add_violation_metric_with_no_data(
        ExecutionContextInterface $context,
        MetricInterface $metric,
        IsNumeric $numericConstraint,
    ): void
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

    public function it_does_not_add_violation_product_price_with_no_data(
        ExecutionContextInterface $context,
        ProductPriceInterface $productPrice,
        IsNumeric $numericConstraint,
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

    public function it_does_not_add_violation_when_validates_numeric_value(ExecutionContextInterface $context, IsNumeric $numericConstraint): void
    {
        $propertyPath = null;
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(5, $numericConstraint);
    }

    public function it_does_not_add_violation_when_validates_numeric_metric_value(
        $context,
        MetricInterface $metric,
        IsNumeric $numericConstraint,
    ): void {
        $metric->getData()->willReturn(5);
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($metric, $numericConstraint);
    }

    public function it_does_not_add_violation_when_validates_numeric_product_price_value(
        $context,
        ProductPriceInterface $productPrice,
        IsNumeric $numericConstraint
    ): void {
        $productPrice->getData()->willReturn(5);
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($productPrice, $numericConstraint);
    }

    public function it_adds_violation_when_validating_non_numeric_value(
        $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $numericConstraint = new IsNumeric();
        $numericConstraint->attributeCode = 'number';

        $context
            ->buildViolation(
                IsNumeric::SHOULD_BE_NUMERIC_MESSAGE,
                [
                    '{{ attribute }}' => $numericConstraint->attributeCode,
                    '{{ value }}' => 'a',
                ]
            )
            ->willReturn($violationBuilder);

        $violationBuilder->setCode(IsNumeric::IS_NUMERIC)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('a', $numericConstraint);
    }

    public function it_adds_violation_when_validating_non_numeric_metric_value(
        $context,
        MetricInterface $metric,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $numericConstraint = new IsNumeric();
        $numericConstraint->attributeCode = 'number';
        $metric->getData()->willReturn('a');

        $context
            ->buildViolation(
                IsNumeric::SHOULD_BE_NUMERIC_MESSAGE,
                [
                    '{{ attribute }}' => $numericConstraint->attributeCode,
                    '{{ value }}' => 'a',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->setCode(IsNumeric::IS_NUMERIC)->willReturn($violationBuilder);
        $violationBuilder->atPath('data')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($metric, $numericConstraint);
    }

    public function it_adds_violation_when_validating_non_numeric_product_price_value(
        $context,
        ProductPriceInterface $productPrice,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $numericConstraint = new IsNumeric();
        $numericConstraint->attributeCode = 'number';
        $productPrice->getData()->willReturn('a');
        $context
            ->buildViolation(
                IsNumeric::SHOULD_BE_NUMERIC_MESSAGE,
                [
                    '{{ attribute }}' => $numericConstraint->attributeCode,
                    '{{ value }}' => 'a',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->setCode(IsNumeric::IS_NUMERIC)->willReturn($violationBuilder);
        $violationBuilder->atPath('data')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($productPrice, $numericConstraint);
    }

    public function it_adds_violation_when_validating_numeric_value_with_space(
        $context,
        MetricInterface $metric,
        ConstraintViolationBuilderInterface $violationBuilder,
    ): void {
        $numericConstraint = new IsNumeric();
        $numericConstraint->attributeCode = 'number';
        $metric->getData()->willReturn(' 3.14');

        $context
            ->buildViolation(
                IsNumeric::SHOULD_NOT_CONTAINS_SPACE_MESSAGE,
                [
                    '{{ attribute }}' => $numericConstraint->attributeCode,
                    '{{ value }}' => ' 3.14',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->setCode(IsNumeric::IS_NUMERIC)->willReturn($violationBuilder);
        $violationBuilder->atPath('data')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($metric, $numericConstraint);
    }
}
