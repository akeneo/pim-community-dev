<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\VariantGroupAxis;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class VariantGroupAxisValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_does_not_validate_if_group_is_not_a_variant_group(
        $context,
        GroupInterface $group,
        GroupType $type,
        VariantGroupAxis $constraint
    ) {
        $group->getId()->willReturn(null);
        $group->getType()->willReturn($type);
        $type->isVariant()->willReturn(false);
        $group->getAxisAttributes()->willReturn([]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($group, $constraint);
    }

    function it_does_not_add_violations_if_variant_group_already_exists(
        $context,
        GroupInterface $variantGroup,
        GroupType $type,
        VariantGroupAxis $constraint
    ) {
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);
        $variantGroup->getAxisAttributes()->willReturn([]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($variantGroup, $constraint);
    }

    function it_does_not_add_violations_if_new_variant_group_contains_axis_attributes(
        $context,
        GroupInterface $variantGroup,
        GroupType $type,
        VariantGroupAxis $constraint,
        AttributeInterface $axisAttribute
    ) {
        $variantGroup->getId()->willReturn(null);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);
        $variantGroup->getAxisAttributes()->willReturn([$axisAttribute]);
        $axisAttribute->getAttributeType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($variantGroup, $constraint);
    }

    function it_adds_a_violation_if_new_variant_group_does_not_contains_axis_attributes(
        $context,
        GroupInterface $variantGroup,
        GroupType $type,
        VariantGroupAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $variantGroup->getId()->willReturn(null);
        $variantGroup->getType()->willReturn($type);
        $variantGroup->getCode()->willReturn('tshirt');
        $type->isVariant()->willReturn(true);
        $variantGroup->getAxisAttributes()->willReturn([]);

        $violationData = [
            '%variant group%' => 'tshirt'
        ];
        $context->buildViolation($constraint->expectedAxisMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($variantGroup, $constraint);
    }

    function it_adds_a_violation_if_a_group_contains_axis_attributes(
        $context,
        GroupInterface $group,
        GroupType $type,
        VariantGroupAxis $constraint,
        AttributeInterface $axis,
        ConstraintViolationBuilderInterface $violation
    ) {
        $group->getId()->willReturn(null);
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('xsell');
        $type->isVariant()->willReturn(false);
        $group->getAxisAttributes()->willReturn([$axis]);

        $violationData = [
            '%group%' => 'xsell'
        ];
        $context->buildViolation($constraint->unexpectedAxisMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($group, $constraint);
    }

    function it_adds_a_violation_if_axis_attributes_are_invalid(
        $context,
        GroupInterface $variantGroup,
        GroupType $type,
        VariantGroupAxis $constraint,
        ConstraintViolationBuilderInterface $violation,
        AttributeInterface $invalidAxis
    ) {
        $variantGroup->getId()->willReturn(12);
        $variantGroup->getType()->willReturn($type);
        $variantGroup->getCode()->willReturn('tshirt');
        $type->isVariant()->willReturn(true);
        $variantGroup->getAxisAttributes()->willReturn([$invalidAxis]);
        $invalidAxis->getAttributeType()->willReturn(AttributeTypes::TEXT);
        $invalidAxis->getCode()->willReturn('name');

        $violationData = [
            '%group%'     => 'tshirt',
            '%attribute%' => 'name'
        ];
        $context->buildViolation($constraint->invalidAxisMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($variantGroup, $constraint);
    }
}
