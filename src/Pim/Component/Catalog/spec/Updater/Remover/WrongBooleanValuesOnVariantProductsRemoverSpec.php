<?php
declare(strict_types=1);

namespace spec\Pim\Component\Catalog\Updater\Remover;

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

class WrongBooleanValuesOnVariantProductsRemoverSpec extends ObjectBehavior
{
    function let(RecursiveValidator $validator, BulkSaverInterface $saver, BulkIndexerInterface $indexer)
    {
        $this->beConstructedWith($validator, $saver,$indexer);
    }

    function it_removes_wrong_boolean_values_on_impacted_variant_products(
        VariantProductInterface $variantProductImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation,
        ValueCollectionInterface $values,
        ValueInterface $booleanValue,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $violationList,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
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

        $validator->validate($variantProductImpacted)->willReturn($violationList);
        $violationList->count()->willReturn(0);

        $saver->saveAll([$variantProductImpacted])->shouldBeCalled();
        $indexer->indexAll([$variantProductImpacted])->shouldBeCalled();

        $this->removeWrongBooleanValues($variantProductImpacted, 1);
    }

    function it_does_not_update_product_without_boolean_in_their_family(
        VariantProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $textAttribute,
        FamilyVariantInterface $familyVariant,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
    ) {
        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$textAttribute]);
        $textAttribute->getType()->willReturn('pim_catalog_text');
        $textAttribute->getCode()->willReturn('text_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $saver->saveAll([$variantProductNotImpacted])->shouldNotBeCalled();
        $indexer->indexAll([$variantProductNotImpacted])->shouldNotBeCalled();

        $this->removeWrongBooleanValues($variantProductNotImpacted, 1);
    }

    function it_does_not_update_product_if_boolean_is_on_product_level(
        VariantProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
    ) {
        $variantProductNotImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductNotImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(1);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $saver->saveAll([$variantProductNotImpacted])->shouldNotBeCalled();
        $indexer->indexAll([$variantProductNotImpacted])->shouldNotBeCalled();

        $this->removeWrongBooleanValues($variantProductNotImpacted, 1);
    }

    function it_does_not_update_product_if_product_does_not_have_any_value_on_this_attribute(
        VariantProductInterface $variantProductNotImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
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

        $saver->saveAll([$variantProductNotImpacted])->shouldNotBeCalled();
        $indexer->indexAll([$variantProductNotImpacted])->shouldNotBeCalled();

        $this->removeWrongBooleanValues($variantProductNotImpacted, 1);
    }

    function it_does_not_update_product_if_product_batch_size_greater_than_nb_products(
        VariantProductInterface $variantProductImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
    ) {
        $variantProductImpacted->getFamily()->willReturn($boots);
        $boots->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $booleanAttribute->getCode()->willReturn('bool_attribute');

        $variantProductImpacted->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('bool_attribute')->willReturn(0);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $variantProductImpacted->getValuesForVariation()->willReturn($valuesForVariation);
        $valuesForVariation->getByCodes('bool_attribute')->willReturn(null);

        $saver->saveAll([$variantProductImpacted])->shouldNotBeCalled();
        $indexer->indexAll([$variantProductImpacted])->shouldNotBeCalled();

        $this->removeWrongBooleanValues($variantProductImpacted, 2);
    }

    function it_throws_an_exception_if_after_being_updated_the_product_is_not_valid(
        VariantProductInterface $variantProductImpacted,
        FamilyInterface $boots,
        AttributeInterface $booleanAttribute,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $valuesForVariation,
        ValueCollectionInterface $values,
        ValueInterface $booleanValue,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $violationList,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
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

        $validator->validate($variantProductImpacted)->willReturn($violationList);
        $violationList->count()->willReturn(1);

        $saver->saveAll([$variantProductImpacted])->shouldNotBeCalled();
        $indexer->indexAll([$variantProductImpacted])->shouldNotBeCalled();

        $variantProductImpacted->getIdentifier()->willReturn('TOTO');

        $this->shouldThrow(\LogicException::class)->during('removeWrongBooleanValues', [$variantProductImpacted, 100]);
    }
}
