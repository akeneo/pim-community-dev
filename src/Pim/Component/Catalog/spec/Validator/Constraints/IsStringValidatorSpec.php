<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\Constraints\IsString;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsStringValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\IsStringValidator');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_does_not_add_violation_null_value($context, IsString $stringConstraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $stringConstraint);
    }

    function it_does_not_add_violation_when_validates_string_value($context, IsString $stringConstraint)
    {
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate('foo', $stringConstraint);
    }

    function it_adds_violation_when_validating_non_string_value(
        $context,
        IsString $stringConstraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context
            ->buildViolation(
                $stringConstraint->message,
                ['%attribute%' => '', '%givenType%' => 'integer']
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(666, $stringConstraint);
    }

    function it_does_not_add_violation_when_validates_string_product_value(
        $context,
        IsString $stringConstraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foo');
        $attribute->getBackendType()->willReturn('text');
        $productValue->getData()->willReturn('bar');

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($productValue, $stringConstraint);
    }

    function it_adds_violation_when_validates_non_string_product_value(
        $context,
        IsString $stringConstraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violation
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foo');
        $attribute->getBackendType()->willReturn('integer');
        $productValue->getData()->willReturn(666);

        $context
            ->buildViolation(
                $stringConstraint->message,
                ['%attribute%' => 'foo', '%givenType%' => 'integer']
            )
            ->shouldBeCalled()
            ->willReturn($violation)
        ;

        $this->validate($productValue, $stringConstraint);
    }
}
