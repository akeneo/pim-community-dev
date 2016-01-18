<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\AttributeTypeForOption;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeTypeForOptionValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_does_not_validate_if_object_is_not_a_product_value(
        $context,
        AttributeTypeForOption $constraint
    ) {
        $object = new \stdClass();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($object, $constraint);
    }

    function it_does_not_add_violations_if_attribute_has_allowed_type(
        $context,
        AttributeOptionInterface $attributeOption,
        AttributeInterface $allowedAttribute,
        AttributeTypeForOption $constraint
    ) {
        $attributeOption->getAttribute()->willReturn($allowedAttribute);
        $allowedAttribute->getAttributeType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeOption, $constraint);
    }

    function it_does_violations_if_attribute_type_is_not_allowed(
        $context,
        AttributeOptionInterface $attributeOption,
        AttributeInterface $notAllowedAttribute,
        AttributeTypeForOption $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attributeOption->getAttribute()->willReturn($notAllowedAttribute);
        $notAllowedAttribute->getAttributeType()->willReturn(AttributeTypes::TEXT);
        $notAllowedAttribute->getCode()->willReturn('attributeCode');

        $violationData = [
            '%attribute%' => 'attributeCode'
        ];
        $context->buildViolation($constraint->invalidAttributeMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($attributeOption, $constraint);
    }
}
