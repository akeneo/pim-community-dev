<?php


namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueVariantGroup;
use Prophecy\Argument;
use Symfony\Component\Validator\ExecutionContextInterface;

class UniqueVariantGroupValidatorSpec extends ObjectBehavior
{
    function let(UniqueVariantGroup $onlyOneVariantGroup, ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_validates_products_with_one_variant_group($context, $onlyOneVariantGroup, ProductInterface $mug, GroupInterface $mugVariantGroup, GroupInterface $otherGroup, GroupTypeInterface $variantType, GroupTypeInterface $groupType)
    {
        $mug->getGroups()->willReturn([$mugVariantGroup, $otherGroup]);

        $mugVariantGroup->getType()->willReturn($variantType);
        $otherGroup->getType()->willReturn($groupType);

        $variantType->isVariant()->willReturn(true);
        $groupType->isVariant()->willReturn(false);

        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($mug, $onlyOneVariantGroup);
    }

    function it_does_not_validate_products_with_multiple_variant_group($context, $onlyOneVariantGroup, ProductInterface $mug, GroupInterface $mugVariantGroup, GroupInterface $otherGroup, GroupTypeInterface $variantType, GroupTypeInterface $groupType)
    {
        $mug->getGroups()->willReturn([$mugVariantGroup, $otherGroup]);

        $mugVariantGroup->getType()->willReturn($variantType);
        $otherGroup->getType()->willReturn($variantType);

        $variantType->isVariant()->willReturn(true);

        $mug->getIdentifier()->willReturn('mug');
        $context->addViolation($onlyOneVariantGroup->message, ['%group_one%' => $mugVariantGroup, '%group_two%' => $otherGroup, '%product%' => 'mug'])->shouldBeCalled();

        $this->validate($mug, $onlyOneVariantGroup);
    }
}
