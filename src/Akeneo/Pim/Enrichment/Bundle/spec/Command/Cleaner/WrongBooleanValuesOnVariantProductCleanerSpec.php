<?php
declare(strict_types=1);

namespace spec\Akeneo\Pim\Enrichment\Bundle\Command\Cleaner;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class WrongBooleanValuesOnVariantProductCleanerSpec extends ObjectBehavior
{
    function it_updates_wrong_boolean_values_on_impacted_variant_products(
        ProductInterface $variantProductImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation,
        ValueCollectionInterface $values,
        ValueInterface $booleanValue
    ) {
        $variantProductImpacted->isVariant()->willReturn(true);
        $variantProductImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $variantProductImpacted->getValuesForVariation()->willReturn($valuesForVariation);
        $valuesForVariation->getByCodes('bool_attribute')->willReturn($booleanValue);

        $variantProductImpacted->getValues()->willReturn($values);
        $values->removeByAttribute($booleanAttribute)->shouldBeCalled();
        $variantProductImpacted->setValues($values)->shouldBeCalled();

        $this->cleanProduct($variantProductImpacted)->shouldReturn(true);
    }

    function it_does_not_update_product_without_boolean_in_their_family(
        ProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $textAttribute,
        FamilyVariantInterface $familyVariant
    ) {
        $variantProductNotImpacted->isVariant()->willReturn(true);
        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$textAttribute]);
        $textAttribute->getType()->willReturn('pim_catalog_text');
        $textAttribute->getCode()->willReturn('text_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $variantProductNotImpacted->getValues()->shouldNotBeCalled();

        $this->cleanProduct($variantProductNotImpacted)->shouldReturn(false);
    }

    function it_does_not_update_product_if_boolean_is_on_product_level(
        ProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant
    ) {
        $variantProductNotImpacted->isVariant()->willReturn(true);
        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(1);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $variantProductNotImpacted->getValues()->shouldNotBeCalled();

        $this->cleanProduct($variantProductNotImpacted)->shouldReturn(false);
    }

    function it_does_not_update_product_if_product_does_not_have_any_value_on_this_attribute(
        ProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation
    ) {
        $variantProductNotImpacted->isVariant()->willReturn(true);
        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $variantProductNotImpacted->getValuesForVariation()->willReturn($valuesForVariation);
        $valuesForVariation->getByCodes('bool_attribute')->willReturn(null);

        $variantProductNotImpacted->getValues()->shouldNotBeCalled();

        $this->cleanProduct($variantProductNotImpacted)->shouldReturn(false);
    }

    function it_does_not_update_product_that_is_not_variant(ProductInterface $productNotImpacted)
    {
        $productNotImpacted->isVariant()->willReturn(false);
        $productNotImpacted->getValues()->shouldNotBeCalled();

        $this->cleanProduct($productNotImpacted)->shouldReturn(false);
    }
}
