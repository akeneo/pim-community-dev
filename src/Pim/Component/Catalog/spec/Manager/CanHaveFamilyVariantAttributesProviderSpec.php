<?php

namespace spec\Pim\Component\Catalog\FamilyVariant;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CanHaveFamilyVariantInterface;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;

class CanHaveFamilyVariantAttributesProviderSpec extends ObjectBehavior
{
    function let(
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $attributeSet,
        CommonAttributeCollection $commonAttributes,
        Collection $attributes,
        Collection $axes,
        AttributeInterface $price,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $color
    ) {
        $variantionLevel = 1;

        $familyVariant->getVariantAttributeSet($variantionLevel)->willReturn($attributeSet);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributes);
        $commonAttributes->toArray()->willReturn([$price]);

        $attributeSet->getAttributes()->willReturn($attributes);
        $attributeSet->getAxes()->willReturn($axes);

        $attributes->toArray()->willReturn([$name, $description, $color]);
        $axes->toArray()->willReturn([$color]);

        $this->beConstructedWith();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\FamilyVariant\CanHaveFamilyVariantAttributesProvider');
    }

    function it_gets_attributes_from_an_non_root_entity_with_a_family_variant(
        $familyVariant,
        $name,
        $description,
        $color,
        CanHaveFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->isRootVariation()->willReturn(false);
        $entity->getVariationLevel()->willReturn(1);

        $this->getAttributes($entity)->shouldReturn([$name, $description, $color]);
    }

    function it_gets_attributes_from_a_root_product_model_entity_with_a_family_variant(
        $familyVariant,
        $price,
        CanHaveFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->isRootVariation()->willReturn(true);

        $this->getAttributes($entity)->shouldReturn([$price]);
    }

    function it_returns_no_attribute_if_the_entity_has_no_family_variant(CanHaveFamilyVariantInterface $entity)
    {
        $entity->getFamilyVariant()->willReturn(null);

        $this->getAttributes($entity)->shouldReturn([]);
    }

    function it_gets_axes_from_an_entity_with_a_family_variant(
        $familyVariant,
        $color,
        CanHaveFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->isRootVariation()->willReturn(false);
        $entity->getVariationLevel()->willReturn(1);

        $this->getAxes($entity)->shouldReturn([$color]);
    }

    function it_returns_no_axis_if_the_entity_has_no_family_variant(CanHaveFamilyVariantInterface $entity)
    {
        $entity->getFamilyVariant()->willReturn(null);

        $this->getAxes($entity)->shouldReturn([]);
    }

    function it_returns_no_axis_if_the_entity_is_a_root_product_model(
        $familyVariant,
        CanHaveFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->isRootVariation()->willReturn(true);

        $this->getAxes($entity)->shouldReturn([]);
    }
}
