<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\VariantGroupAxisValidator;
use Pim\Bundle\CatalogBundle\Validator\Constraints\VariantGroupAxis;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ExecutionContextInterface;

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
        Constraint $constraint
    ) {
        $group->getId()->willReturn(null);
        $group->getType()->willReturn($type);
        $type->isVariant()->willReturn(false);
        $group->getAxisAttributes()->willReturn([]);
        $context->addViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate($group, $constraint);
    }

    function it_does_not_add_violations_if_variant_group_already_exists(
        $context,
        GroupInterface $variantGroup,
        GroupType $type,
        Constraint $constraint
    ) {
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);
        $variantGroup->getAxisAttributes()->willReturn([]);
        $context->addViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate($variantGroup, $constraint);
    }

    function it_does_not_add_violations_if_new_variant_group_contains_axis_attributes(
        $context,
        GroupInterface $variantGroup,
        GroupType $type,
        Constraint $constraint,
        AttributeInterface $axisAttribute
    ) {
        $variantGroup->getId()->willReturn(null);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);
        $variantGroup->getAxisAttributes()->willReturn([$axisAttribute]);
        $context->addViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate($variantGroup, $constraint);
    }

    function it_adds_a_violation_if_new_variant_group_does_not_contains_axis_attributes(
        $context,
        GroupInterface $variantGroup,
        GroupType $type,
        VariantGroupAxis $constraint
    ) {
        $variantGroup->getId()->willReturn(null);
        $variantGroup->getType()->willReturn($type);
        $variantGroup->getCode()->willReturn('tshirt');
        $type->isVariant()->willReturn(true);
        $variantGroup->getAxisAttributes()->willReturn([]);

        $violationData = [
            '%variant group%' => 'tshirt'
        ];
        $context->addViolation($constraint->message, $violationData)->shouldBeCalled();

        $this->validate($variantGroup, $constraint);
    }
}
