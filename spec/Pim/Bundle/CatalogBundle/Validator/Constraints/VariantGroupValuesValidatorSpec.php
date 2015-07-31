<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\VariantGroupValues;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class VariantGroupValuesValidatorSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository, ExecutionContextInterface $context)
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

    function it_does_not_add_violations_if_variant_group_template_does_not_contain_axis_or_unique_attributes(
        GroupInterface $variantGroup,
        GroupType $type,
        ProductTemplateInterface $template,
        Constraint $constraint,
        $attributeRepository,
        AttributeInterface $axisAttribute,
        $context
    ) {
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);

        $variantGroup->getProductTemplate()->willReturn($template);
        $variantGroup->getAxisAttributes()->willReturn([$axisAttribute]);
        $attributeRepository->findUniqueAttributeCodes()->willReturn(['sku', 'barcode']);

        $template->getValuesData()->willReturn([]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($variantGroup, $constraint);
    }

    function it_adds_a_violation_if_variant_group_template_contains_an_axis_attribute(
        GroupInterface $variantGroup,
        GroupType $type,
        ProductTemplateInterface $template,
        VariantGroupValues $constraint,
        $attributeRepository,
        AttributeInterface $axisAttribute,
        $context,
        ConstraintViolationBuilderInterface $violation
    ) {
        $variantGroup->getType()->willReturn($type);
        $variantGroup->getCode()->willReturn('tshirt');
        $type->isVariant()->willReturn(true);

        $variantGroup->getProductTemplate()->willReturn($template);
        $variantGroup->getAxisAttributes()->willReturn([$axisAttribute]);
        $axisAttribute->getCode()->willReturn('size');
        $attributeRepository->findUniqueAttributeCodes()->willReturn(['sku', 'barcode']);

        $template->getValuesData()->willReturn(['size' => 'M']);

        $violationData = [
            '%group%'      => 'tshirt',
            '%attributes%' => '"size"'
        ];
        $context->buildViolation($constraint->message, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($variantGroup, $constraint);
    }

    function it_adds_a_violation_if_variant_group_template_contains_a_unique_attribute(
        GroupInterface $variantGroup,
        GroupType $type,
        ProductTemplateInterface $template,
        VariantGroupValues $constraint,
        $attributeRepository,
        $context,
        ConstraintViolationBuilderInterface $violation
    ) {
        $variantGroup->getType()->willReturn($type);
        $variantGroup->getCode()->willReturn('tshirt');
        $type->isVariant()->willReturn(true);

        $variantGroup->getProductTemplate()->willReturn($template);
        $variantGroup->getAxisAttributes()->willReturn([]);
        $attributeRepository->findUniqueAttributeCodes()->willReturn(['sku', 'barcode']);

        $template->getValuesData()->willReturn(['sku' => 'SKU-001']);

        $violationData = [
            '%group%'      => 'tshirt',
            '%attributes%' => '"sku"'
        ];
        $context->buildViolation($constraint->message, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($variantGroup, $constraint);

        $template->getValuesData()->willReturn(['sku' => 'SKU-001', 'barcode' => 001122334455]);

        $violationData = [
            '%group%'      => 'tshirt',
            '%attributes%' => '"sku", "barcode"'
        ];
        $context->buildViolation($constraint->message, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($variantGroup, $constraint);
    }
}
