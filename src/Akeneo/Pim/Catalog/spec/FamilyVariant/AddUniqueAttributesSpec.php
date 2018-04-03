<?php

namespace spec\Pim\Component\Catalog\FamilyVariant;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\FamilyVariant\AddUniqueAttributes;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Prophecy\Argument;

class AddUniqueAttributesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AddUniqueAttributes::class);
    }

    function it_adds_unique_attributes_for_family_variant(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $ean,
        AttributeInterface $name,
        AttributeInterface $size,
        AttributeInterface $sku,
        ArrayCollection $familyAttributes,
        \Iterator $familyAttributeIterator,
        ArrayCollection $familyVariantAttributes,
        ArrayCollection $attributeCodesCollection,
        VariantAttributeSetInterface $variantAttributeSet
    ) {
        $familyVariant->getFamily()->willReturn($family);

        $ean->getCode()->willReturn('ean');
        $ean->getType()->willReturn(AttributeTypes::TEXT);
        $ean->isUnique()->willReturn(true);

        $name->getCode()->willReturn('name');
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $name->isUnique()->willReturn(false);

        $name->getCode()->willReturn('size');
        $name->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $name->isUnique()->willReturn(false);

        $sku->getCode()->willReturn('sku');
        $sku->getType()->willReturn(AttributeTypes::IDENTIFIER);

        $family->getAttributes()->willReturn($familyAttributes);
        $familyAttributes->getIterator()->willReturn($familyAttributeIterator);
        $familyAttributeIterator->rewind()->shouldBeCalled();
        $familyAttributeIterator->valid()->willReturn(true, true, true, true, false);
        $familyAttributeIterator->current()->willReturn($ean, $name, $size, $sku);
        $familyAttributeIterator->next()->shouldBeCalled();

        $familyVariant->getAttributes()->willReturn($familyVariantAttributes);
        $familyVariantAttributes->map(Argument::type(\Closure::class))->willReturn($attributeCodesCollection);
        $attributeCodesCollection->toArray()->willReturn(['size', 'sku']);

        $familyVariant->getNumberOfLevel()->willReturn(1);
        $familyVariant->getVariantAttributeSet(1)->willReturn($variantAttributeSet);

        $variantAttributeSet->addAttribute($ean)->shouldBeCalled();

        $this->addToFamilyVariant($familyVariant);
    }
}
