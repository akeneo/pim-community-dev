<?php

namespace spec\Pim\Component\Catalog\Model;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariant;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyVariantSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariant::class);
    }

    function it_is_a_family_variant()
    {
        $this->shouldImplement(FamilyVariantInterface::class);
    }

    function its_code_is_translatable()
    {
        $this->shouldImplement(TranslatableInterface::class);
    }

    function it_gets_common_attribute_set(
        VariantAttributeSetInterface $variantAttributeSet1,
        VariantAttributeSetInterface $variantAttributeSet2,
        FamilyInterface $family,
        Collection $familyAttributes,
        AttributeInterface $name,
        AttributeInterface $color,
        AttributeInterface $size,
        Collection $axes1,
        Collection $axes2,
        Collection $attribute1,
        Collection $attribute2,
        \Iterator $iterator
    ) {
        $this->addVariantAttributeSet($variantAttributeSet1);
        $this->addVariantAttributeSet($variantAttributeSet2);
        $this->setFamily($family);

        $family->getAttributes()->willReturn($familyAttributes);
        $familyAttributes->toArray()->willReturn([$name, $color, $size]);

        $axes1->getIterator()->willReturn($iterator);
        $variantAttributeSet1->getAxes()->willReturn($axes1);
        $attribute1->getIterator()->willReturn($iterator);
        $variantAttributeSet1->getAttributes()->willReturn($attribute1);
        $variantAttributeSet1->getLevel()->willReturn(1);

        $axes2->getIterator()->willReturn($iterator);
        $variantAttributeSet2->getAxes()->willReturn($axes2);

        $attribute2->getIterator()->willReturn($iterator);
        $variantAttributeSet2->getAttributes()->willReturn($attribute2);
        $variantAttributeSet2->getLevel()->willReturn(2);

        $this->getCommonAttributes()->shouldHaveType(CommonAttributeCollection::class);
    }

    function it_adds_variant_attribute_set(
        VariantAttributeSetInterface $variantAttributeSet1,
        VariantAttributeSetInterface $variantAttributeSet2
    ) {
        $variantAttributeSet1->getLevel()->willReturn(1);
        $variantAttributeSet2->getLevel()->willReturn(2);

        $this->addVariantAttributeSet($variantAttributeSet1)->shouldReturn(null);
        $this->addVariantAttributeSet($variantAttributeSet2)->shouldReturn(null);

        $this->getVariantAttributeSet(1)->shouldReturn($variantAttributeSet1);
        $this->getVariantAttributeSet(2)->shouldReturn($variantAttributeSet2);
    }

    function it_has_axes(
        VariantAttributeSetInterface $variantAttributeSet1,
        VariantAttributeSetInterface $variantAttributeSet2,
        ArrayCollection $axes1,
        ArrayCollection $axes2,
        AttributeInterface $color,
        AttributeInterface $size
    ) {
        $this->addVariantAttributeSet($variantAttributeSet1);
        $this->addVariantAttributeSet($variantAttributeSet2);

        $variantAttributeSet1->getAxes()->willReturn($axes1);
        $variantAttributeSet2->getAxes()->willReturn($axes2);

        $axes1->toArray()->willReturn([$color]);
        $axes2->toArray()->willReturn([$size]);

        $axes = $this->getAxes();
        $axes->shouldHaveType(ArrayCollection::class);
        $axes->toArray([$color, $size]);
    }

    function it_has_attributes(
        VariantAttributeSetInterface $commonAttributeSet,
        VariantAttributeSetInterface $variantAttributeSet1,
        VariantAttributeSetInterface $variantAttributeSet2,
        ArrayCollection $commonAttributes,
        ArrayCollection $attributes1,
        ArrayCollection $attributes2,
        AttributeInterface $name,
        AttributeInterface $color,
        AttributeInterface $size
    ) {
        $this->addVariantAttributeSet($variantAttributeSet1);
        $this->addVariantAttributeSet($variantAttributeSet2);

        $commonAttributeSet->getAttributes()->willReturn($commonAttributes);
        $variantAttributeSet1->getAttributes()->willReturn($attributes1);
        $variantAttributeSet2->getAttributes()->willReturn($attributes2);

        $commonAttributes->toArray()->willReturn([$name]);
        $attributes1->toArray()->willReturn([$color]);
        $attributes2->toArray()->willReturn([$size]);

        $axes = $this->getAttributes();
        $axes->shouldHaveType(ArrayCollection::class);
        $axes->toArray([$color, $size]);
    }

    function it_throws_an_exception_if_variant_attribute_set_index_is_invalid()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('getVariantAttributeSet', [
            0,
        ]);
    }
}
