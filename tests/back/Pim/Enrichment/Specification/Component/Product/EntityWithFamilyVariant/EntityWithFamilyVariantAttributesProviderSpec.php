<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;

class EntityWithFamilyVariantAttributesProviderSpec extends ObjectBehavior
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
        $variationLevel = 1;

        $familyVariant->getVariantAttributeSet($variationLevel)->willReturn($attributeSet);
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
        $this->shouldHaveType(EntityWithFamilyVariantAttributesProvider::class);
    }

    function it_gets_attributes_from_an_non_root_entity_with_a_family_variant(
        $familyVariant,
        $name,
        $description,
        $color,
        EntityWithFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getVariationLevel()->willReturn(1);

        $this->getAttributes($entity)->shouldReturn([$name, $description, $color]);
    }

    function it_gets_attributes_from_a_root_product_model_entity_with_a_family_variant(
        $familyVariant,
        $price,
        EntityWithFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getVariationLevel()->willReturn(EntityWithFamilyVariantInterface::ROOT_VARIATION_LEVEL);

        $this->getAttributes($entity)->shouldReturn([$price]);
    }

    function it_returns_no_attribute_if_the_entity_has_no_family_variant(EntityWithFamilyVariantInterface $entity)
    {
        $entity->getFamilyVariant()->willReturn(null);

        $this->getAttributes($entity)->shouldReturn([]);
    }

    function it_returns_no_attribute_if_the_family_variant_of_the_entity_has_no_attribute_set(
        $familyVariant,
        EntityWithFamilyVariantInterface $entity
    ) {
        $entity->getVariationLevel()->willReturn(1);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn(null);

        $this->getAttributes($entity)->shouldReturn([]);
    }

    function it_gets_axes_from_an_entity_with_a_family_variant(
        $familyVariant,
        $color,
        EntityWithFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getVariationLevel()->willReturn(1);

        $this->getAxes($entity)->shouldReturn([$color]);
    }

    function it_returns_no_axis_if_the_entity_has_no_family_variant(EntityWithFamilyVariantInterface $entity)
    {
        $entity->getVariationLevel()->willReturn(1);
        $entity->getFamilyVariant()->willReturn(null);

        $this->getAxes($entity)->shouldReturn([]);
    }

    function it_returns_no_axis_if_the_entity_is_a_root_product_model(
        $familyVariant,
        EntityWithFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getVariationLevel()->willReturn(EntityWithFamilyVariantInterface::ROOT_VARIATION_LEVEL);

        $this->getAxes($entity)->shouldReturn([]);
    }
}
