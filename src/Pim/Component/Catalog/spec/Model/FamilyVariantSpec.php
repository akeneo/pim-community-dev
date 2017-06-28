<?php

namespace spec\Pim\Component\Catalog\Model;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeSetInterface;
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

    function it_is_a_variant_family()
    {
        $this->shouldImplement(FamilyVariantInterface::class);
    }

    function its_code_is_translatable()
    {
        $this->shouldImplement(TranslatableInterface::class);
    }

    function it_adds_common_attribute_set(AttributeSetInterface $variantAttributeSets)
    {
        $this->addCommonAttributeSet($variantAttributeSets)->shouldReturn(null);
        $this->getCommonAttributeSet()->shouldReturn($variantAttributeSets);
    }

    function it_adds_variant_attribute_set(
        AttributeSetInterface $commonAttributeSet,
        AttributeSetInterface $variantAttributeSet1,
        AttributeSetInterface $variantAttributeSet2
    ) {
        $this->addCommonAttributeSet($commonAttributeSet)->shouldReturn(null);
        $this->addVariantAttributeSet(1, $variantAttributeSet1)->shouldReturn(null);
        $this->addVariantAttributeSet(2, $variantAttributeSet2)->shouldReturn(null);

        $this->getVariantAttributeSet(1)->shouldReturn($variantAttributeSet1);
        $this->getVariantAttributeSet(2)->shouldReturn($variantAttributeSet2);
    }

    function it_has_axes(
        AttributeSetInterface $commonAttributeSet,
        AttributeSetInterface $variantAttributeSet1,
        AttributeSetInterface $variantAttributeSet2,
        ArrayCollection $axes1,
        ArrayCollection $axes2,
        AttributeInterface $color,
        AttributeInterface $size
    ) {
        $this->addCommonAttributeSet($commonAttributeSet);
        $this->addVariantAttributeSet(1, $variantAttributeSet1);
        $this->addVariantAttributeSet(2, $variantAttributeSet2);

        $variantAttributeSet1->getAxes()->willReturn($axes1);
        $variantAttributeSet2->getAxes()->willReturn($axes2);

        $axes1->toArray()->willReturn([$color]);
        $axes2->toArray()->willReturn([$size]);

        $axes = $this->getAxes();
        $axes->shouldHaveType(ArrayCollection::class);
        $axes->toArray([$color, $size]);
    }

    function it_has_attributes(
        AttributeSetInterface $commonAttributeSet,
        AttributeSetInterface $variantAttributeSet1,
        AttributeSetInterface $variantAttributeSet2,
        ArrayCollection $commonAttributes,
        ArrayCollection $attributes1,
        ArrayCollection $attributes2,
        AttributeInterface $name,
        AttributeInterface $color,
        AttributeInterface $size
    ) {
        $this->addCommonAttributeSet($commonAttributeSet);
        $this->addVariantAttributeSet(1, $variantAttributeSet1);
        $this->addVariantAttributeSet(2, $variantAttributeSet2);

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

    function it_throws_an_exception_if_variant_attribute_set_index_is_invalid(
        AttributeSetInterface $variantAttributeSets
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)->during('addVariantAttributeSet', [
            0,
            $variantAttributeSets
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('getVariantAttributeSet', [
            0,
        ]);
    }
}
