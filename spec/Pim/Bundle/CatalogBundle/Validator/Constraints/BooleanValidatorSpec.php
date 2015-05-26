<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Boolean;
use Prophecy\Argument;
use Symfony\Component\Validator\ExecutionContextInterface;

class BooleanValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\BooleanValidator');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_does_not_add_violation_null_value($context, Boolean $constraint)
    {
        $context
            ->addViolationAt(Argument::cetera())
            ->shouldNotBeCalled();
        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_does_not_add_violation_when_validates_boolean_value($context, Boolean $constraint)
    {
        $context
            ->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(true, $constraint);
        $this->validate(false, $constraint);
    }

    function it_does_not_add_violation_when_validates_boolean_like_value($context, Boolean $constraint)
    {
        $context
            ->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate(1, $constraint);
        $this->validate(0, $constraint);
        $this->validate('1', $constraint);
        $this->validate('0', $constraint);
    }

    function it_adds_violation_when_validating_non_boolean_value($context, Boolean $constraint)
    {
        $context
            ->addViolation(
                $constraint->message,
                ['%attribute%' => '', '%givenType%' => 'integer']
            )
            ->shouldBeCalled();

        $this->validate(666, $constraint);

        $context
            ->addViolation(
                $constraint->message,
                ['%attribute%' => '', '%givenType%' => 'string']
            )
            ->shouldBeCalled();

        $this->validate('foo', $constraint);
        $this->validate('true', $constraint);

        $context
            ->addViolation(
                $constraint->message,
                ['%attribute%' => '', '%givenType%' => 'array']
            )
            ->shouldBeCalled();

        $this->validate(['foo'], $constraint);
        $this->validate([true], $constraint);
    }

    function it_does_not_add_violation_when_validates_boolean_product_value(
        $context,
        Boolean $constraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foo');
        $attribute->getBackendType()->willReturn('boolean');
        $productValue->getBoolean()->willReturn(true);

        $context
            ->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($productValue, $constraint);
    }

    function it_adds_violation_when_validates_null_product_value(
        $context,
        Boolean $constraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foo');
        $attribute->getBackendType()->willReturn('integer');
        $productValue->getInteger()->willReturn(null);

        $context
            ->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($productValue, $constraint);
    }

    function it_adds_violation_when_validates_non_boolean_product_value(
        $context,
        Boolean $constraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foo');
        $attribute->getBackendType()->willReturn('integer');
        $productValue->getInteger()->willReturn(666);

        $context
            ->addViolation(
                $constraint->message,
                ['%attribute%' => 'foo', '%givenType%' => 'integer']
            )
            ->shouldBeCalled();

        $this->validate($productValue, $constraint);
    }
}
