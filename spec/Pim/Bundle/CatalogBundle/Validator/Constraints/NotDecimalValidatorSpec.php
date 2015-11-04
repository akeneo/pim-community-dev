<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimal;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotDecimalValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimalValidator');
    }

    function it_validates_numeric_value($context, NotDecimal $constraint)
    {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(100, $constraint);
    }

    function it_does_not_validate_decimal_value(
        $context,
        NotDecimal $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(100.5, $constraint);
    }

    function it_validates_string_value($context, NotDecimal $constraint)
    {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('100', $constraint);
    }

    function it_does_not_validate_string_value(
        $context,
        NotDecimal $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate('100.5', $constraint);
    }

    function it_validates_a_product_media($context, NotDecimal $constraint, ProductPriceInterface $productPrice)
    {
        $productPrice->getData()->willReturn(520);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productPrice, $constraint);
    }

    function it_does_not_validate_a_product_media(
        $context,
        NotDecimal $constraint,
        ProductPriceInterface $productPrice,
        ConstraintViolationBuilderInterface $violation
    ) {
        $productPrice->getData()->willReturn(520.55);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($productPrice, $constraint);
    }

    function it_validates_a_metric($context, NotDecimal $constraint, MetricInterface $metric)
    {
        $metric->getData()->willReturn(82);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($metric, $constraint);
    }

    function it_does_not_validate_a_metric(
        $context,
        NotDecimal $constraint,
        MetricInterface $metric,
        ConstraintViolationBuilderInterface $violation
    ) {
        $metric->getData()->willReturn(82.25);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($metric, $constraint);
    }

    function it_validates_nullable_value($context, NotDecimal $constraint)
    {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }
}
