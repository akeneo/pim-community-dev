<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\StringConstraint;
use Prophecy\Argument;
use Symfony\Component\Validator\ExecutionContextInterface;

class StringValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\StringValidator');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_does_not_add_violation_null_value($context, StringConstraint $stringConstraint)
    {
        $context
            ->addViolationAt(Argument::cetera())
            ->shouldNotBeCalled();
        $context
            ->addViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $stringConstraint);
    }

    function it_does_not_add_violation_when_validates_string_value($context, StringConstraint $stringConstraint)
    {
        $context
            ->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate('foo', $stringConstraint);
    }

    function it_adds_violation_when_validating_non_string_value($context, StringConstraint $stringConstraint)
    {
        $context
            ->addViolation(
                $stringConstraint->message,
                ['%attribute%' => '', '%givenType%' => 'integer']
            )
            ->shouldBeCalled();

        $this->validate(666, $stringConstraint);
    }

    function it_does_not_add_violation_when_validates_string_product_value(
        $context,
        StringConstraint $stringConstraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {

        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foo');
        $attribute->getBackendType()->willReturn('varchar');
        $productValue->getVarchar()->willReturn('bar');

        $context
            ->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($productValue, $stringConstraint);
    }

    function it_adds_violation_when_validates_non_string_product_value(
        $context,
        StringConstraint $stringConstraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {

        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foo');
        $attribute->getBackendType()->willReturn('integer');
        $productValue->getInteger()->willReturn(666);

        $context
            ->addViolation(
                $stringConstraint->message,
                ['%attribute%' => 'foo', '%givenType%' => 'integer']
            )
            ->shouldBeCalled();

        $this->validate($productValue, $stringConstraint);
    }
}
