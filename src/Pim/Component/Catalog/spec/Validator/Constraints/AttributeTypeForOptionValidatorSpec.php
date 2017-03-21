<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Validator\Constraints\AttributeTypeForOption;
use Prophecy\Argument;
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
        $allowedAttribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
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
        $notAllowedAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $notAllowedAttribute->getCode()->willReturn('attributeCode');
        $constraint->propertyPath = 'path';

        $violationData = [
            '%attribute%'       => 'attributeCode',
            '%attribute_types%' => 'pim_catalog_simpleselect", "pim_catalog_multiselect',
        ];
        $context->buildViolation($constraint->invalidAttributeMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->atPath('path')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attributeOption, $constraint);
    }

    function it_does_not_add_violations_if_attribute_is_null(
        $context,
        AttributeOptionInterface $attributeOption,
        AttributeTypeForOption $constraint
    ) {
        $attributeOption->getAttribute()->willReturn(null);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeOption, $constraint);
    }
}
