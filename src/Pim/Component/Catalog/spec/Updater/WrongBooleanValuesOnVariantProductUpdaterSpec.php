<?php
declare(strict_types=1);

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WrongBooleanValuesOnVariantProductUpdaterSpec extends ObjectBehavior
{
    function it_updates_wrong_boolean_values_on_impacted_variant_products(
        VariantProductInterface $variantProductImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation,
        ValueCollectionInterface $values,
        ValueInterface $booleanValue
    ) {
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

        $this->updateProduct($variantProductImpacted);
    }

    function it_does_not_update_product_without_boolean_in_their_family(
        VariantProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $textAttribute,
        FamilyVariantInterface $familyVariant
    ) {
        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$textAttribute]);
        $textAttribute->getType()->willReturn('pim_catalog_text');
        $textAttribute->getCode()->willReturn('text_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $this->updateProduct($variantProductNotImpacted);
    }

    function it_does_not_update_product_if_boolean_is_on_product_level(
        VariantProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant
    ) {
        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(1);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $this->updateProduct($variantProductNotImpacted);
    }

    function it_does_not_update_product_if_product_does_not_have_any_value_on_this_attribute(
        VariantProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation
    ) {
        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $variantProductNotImpacted->getValuesForVariation()->willReturn($valuesForVariation);
        $valuesForVariation->getByCodes('bool_attribute')->willReturn(null);

        $this->updateProduct($variantProductNotImpacted);
    }
}
