<?php

namespace spec\PimEnterprise\Component\Workflow\Model;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

class PublishedProductSpec extends ObjectBehavior
{
    function it_gets_the_label_of_the_product_without_specified_scope(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);
        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $nameValue->getData()->willReturn('Petit outil agricole authentique');

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('Petit outil agricole authentique');
    }

    function it_gets_the_label_regardless_of_the_specified_scope_if_the_attribute_as_label_is_not_scopable(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);
        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $nameValue->getData()->willReturn('Petit outil agricole authentique');

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petit outil agricole authentique');
    }

    function it_gets_the_label_if_the_scope_is_specified_and_the_attribute_as_label_is_scopable(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->getByCodes('name', 'mobile', 'fr_FR')->willReturn($nameValue);
        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $nameValue->getData()->willReturn('Petite pelle');

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);
        $this->setScope('mobile');

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petite pelle');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_family(
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $this->setFamily(null);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_attribute_as_label(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $identifier
    ) {
        $family->getAttributeAsLabel()->willReturn(null);
        $family->getId()->willReturn(42);

        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_the_label_value_is_null(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();
        $values->getByCodes('name', null, 'fr_FR')->willReturn(null);

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_the_label_value_data_is_empty(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);
        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $nameValue->getData()->willReturn(null);

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_is_not_a_variant_product()
    {
        $this->isVariant()->shouldReturn(false);
    }

    function it_is_a_variant_product(ProductModelInterface $parent)
    {
        $this->setParent($parent);
        $this->isVariant()->shouldReturn(true);
    }

    function it_has_the_values_of_the_variation(
        ValueCollectionInterface $valueCollection
    ) {
        $this->setValues($valueCollection);

        $this->getValuesForVariation()->shouldBeLike($valueCollection);
    }

    function it_has_values_when_it_is_not_variant(
        ValueCollectionInterface $valueCollection
    ) {
        $this->setValues($valueCollection);
        $this->setParent(null);

        $this->getValues()->shouldBeLike($valueCollection);
    }

    function it_has_values_of_its_parent_when_it_is_variant(
        ValueCollectionInterface $valueCollection,
        ProductModelInterface $productModel,
        ValueCollectionInterface $parentValuesCollection,
        \Iterator $iterator,
        ValueInterface $value,
        AttributeInterface $valueAttribute,
        ValueInterface $otherValue,
        AttributeInterface $otherValueAttribute
    ) {
        $this->setValues($valueCollection);
        $this->setParent($productModel);

        $valueCollection->toArray()->willReturn([$value]);

        $valueAttribute->getCode()->willReturn('value');
        $valueAttribute->isUnique()->willReturn(false);
        $value->getAttribute()->willReturn($valueAttribute);
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn(null);

        $otherValueAttribute->getCode()->willReturn('otherValue');
        $otherValueAttribute->isUnique()->willReturn(false);
        $otherValue->getAttribute()->willReturn($otherValueAttribute);
        $otherValue->getScope()->willReturn(null);
        $otherValue->getLocale()->willReturn(null);

        $productModel->getParent()->willReturn(null);
        $productModel->getValuesForVariation()->willReturn($parentValuesCollection);
        $parentValuesCollection->getIterator()->willreturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($otherValue);
        $iterator->next()->shouldBeCalled();

        $values = $this->getValues();
        $values->toArray()->shouldBeLike(
            [
                'value-<all_channels>-<all_locales>'      => $value,
                'otherValue-<all_channels>-<all_locales>' => $otherValue
            ]
        );
    }

    function it_has_a_variation_level(ProductModelInterface $productModel)
    {
        $this->setParent($productModel);
        $productModel->getVariationLevel()->willReturn(7);
        $this->getVariationLevel()->shouldReturn(8);
    }

    function it_has_a_product_model(ProductModelInterface $productModel)
    {
        $this->setParent($productModel);
        $this->getParent()->shouldReturn($productModel);
    }

    function it_has_a_family_variant(FamilyVariantInterface $familyVariant)
    {
        $this->setFamilyVariant($familyVariant);
        $this->getFamilyVariant()->shouldReturn($familyVariant);
    }
}
