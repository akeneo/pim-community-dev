<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\VariantGroupValues;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ExecutionContextInterface;

class VariantGroupValuesValidatorSpec extends ObjectBehavior
{
    function let(AttributeRepository $attributeRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($attributeRepository);
        $this->initialize($context);
    }

    function it_does_not_validate_if_group_is_not_a_variant_group(
        GroupInterface $group,
        GroupType $type,
        Constraint $constraint
    ) {
        $group->getType()->willReturn($type);
        $type->isVariant()->willReturn(false);
        $group->getProductTemplate()->shouldNotBeCalled();
        $this->validate($group, $constraint);
    }

    function it_does_not_validate_if_variant_group_has_no_template(
        GroupInterface $variantGroup,
        GroupType $type,
        Constraint $constraint
    ) {
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);
        $variantGroup->getProductTemplate()->willReturn(null);
        $variantGroup->getAxisAttributes()->shouldNotBeCalled();

        $this->validate($variantGroup, $constraint);
    }

    function it_does_not_add_violations_if_variant_group_template_does_not_contains_axis_or_identifier(
        GroupInterface $variantGroup,
        GroupType $type,
        ProductTemplateInterface $template,
        Constraint $constraint,
        $attributeRepository,
        AttributeInterface $identifierAttribute,
        AttributeInterface $axisAttribute,
        ArrayCollection $axisCollection,
        $context
    ) {
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);

        $variantGroup->getProductTemplate()->willReturn($template);
        $variantGroup->getAxisAttributes()->willReturn($axisCollection);
        $axisCollection->toArray()->willReturn([$axisAttribute]);
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);

        $template->hasValueForAttribute($axisAttribute)->willReturn(false);
        $template->hasValueForAttribute($identifierAttribute)->willReturn(false);

        $context->addViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate($variantGroup, $constraint);
    }

    function it_adds_a_violation_if_variant_group_template_contains_an_axis_attribute(
        GroupInterface $variantGroup,
        GroupType $type,
        ProductTemplateInterface $template,
        VariantGroupValues $constraint,
        $attributeRepository,
        AttributeInterface $identifierAttribute,
        AttributeInterface $axisAttribute,
        ArrayCollection $axisCollection,
        $context
    ) {
        $variantGroup->getType()->willReturn($type);
        $variantGroup->getCode()->willReturn('tshirt');
        $type->isVariant()->willReturn(true);

        $variantGroup->getProductTemplate()->willReturn($template);
        $variantGroup->getAxisAttributes()->willReturn($axisCollection);
        $axisCollection->toArray()->willReturn([$axisAttribute]);
        $axisAttribute->getCode()->willReturn('size');
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);

        $template->hasValueForAttribute($axisAttribute)->willReturn(true);
        $template->hasValueForAttribute($identifierAttribute)->willReturn(false);

        $violationData = [
            '%variant group%' => 'tshirt',
            '%attributes%'    => 'size'
        ];
        $context->addViolation($constraint->message, $violationData)->shouldBeCalled();

        $this->validate($variantGroup, $constraint);
    }

    function it_adds_a_violation_if_variant_group_template_contains_an_identifier_attribute(
        GroupInterface $variantGroup,
        GroupType $type,
        ProductTemplateInterface $template,
        VariantGroupValues $constraint,
        $attributeRepository,
        AttributeInterface $identifierAttribute,
        AttributeInterface $axisAttribute,
        ArrayCollection $axisCollection,
        $context
    ) {
        $variantGroup->getType()->willReturn($type);
        $variantGroup->getCode()->willReturn('tshirt');
        $type->isVariant()->willReturn(true);

        $variantGroup->getProductTemplate()->willReturn($template);
        $variantGroup->getAxisAttributes()->willReturn($axisCollection);
        $axisCollection->toArray()->willReturn([$axisAttribute]);
        $axisAttribute->getCode()->willReturn('size');
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $identifierAttribute->getCode()->willReturn('sku');

        $template->hasValueForAttribute($axisAttribute)->willReturn(false);
        $template->hasValueForAttribute($identifierAttribute)->willReturn(true);

        $violationData = [
            '%variant group%' => 'tshirt',
            '%attributes%'    => 'sku'
        ];
        $context->addViolation($constraint->message, $violationData)->shouldBeCalled();

        $this->validate($variantGroup, $constraint);
    }
}
