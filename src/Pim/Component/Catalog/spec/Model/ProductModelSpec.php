<?php

namespace spec\Pim\Component\Catalog\Model;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\TimestampableInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Value;

class ProductModelSpec extends ObjectBehavior
{
    function it_is_a_product_model()
    {
        $this->shouldHaveType(ProductModel::class);
        $this->shouldImplement(ProductModelInterface::class);
    }

    function it_is_an_entity_with_values()
    {
        $this->shouldImplement(EntityWithValuesInterface::class);
    }

    function it_is_a_timestampable_entity()
    {
        $this->shouldImplement(TimestampableInterface::class);
    }

    function it_is_a_versionable_entity()
    {
        $this->shouldImplement(VersionableInterface::class);
    }

    function it_is_a_category_aware_entity()
    {
        $this->shouldImplement(CategoryAwareInterface::class);
    }

    function it_adds_a_value(
        ValueCollectionInterface $values,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $this->setValues($values);

        $attribute->getCode()->willReturn('foobar');
        $attribute->isUnique()->willReturn(false);

        $value->getAttribute()->willReturn($attribute);
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);

        $values->add($value)->shouldBeCalled();

        $this->addValue($value)->shouldReturn($this);
    }

    function it_removes_a_value(
        ValueCollectionInterface $values,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $this->setValues($values);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foobar');
        $attribute->isUnique()->willReturn(false);

        $this->removeValue($value)->shouldReturn($this);
    }

    function it_gets_the_codes_of_the_product_model_categories(
        CategoryInterface $categorie
    ) {
        $this->addCategory($categorie);

        $categorie->getCode()->willReturn('foobar');

        $this->getCategoryCodes()->shouldReturn(['foobar']);
    }

    function it_gets_the_label_of_the_product_model(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $nameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);
        $nameValue->getData()->willReturn('Petit outil agricole authentique');

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR')->shouldReturn('Petit outil agricole authentique');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_attribute_as_label(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        ValueCollectionInterface $values
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_the_label_value_is_null(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);

        $values->getByCodes('name', null, 'fr_FR')->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_the_label_value_data_is_empty(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $nameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);
        $nameValue->getData()->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_image_of_the_product_model(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsImage,
        ValueCollectionInterface $values,
        ValueInterface $pictureValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $attributeAsImage->getCode()->willReturn('picture');

        $values->getByCodes('picture', null, null)->willReturn($pictureValue);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);

        $this->getImage()->shouldReturn($pictureValue);
    }

    function it_gets_no_image_if_there_is_no_attribute_as_image(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        ValueCollectionInterface $values
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsImage()->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);

        $this->getImage()->shouldReturn(null);
    }

    function it_gets_no_image_if_the_value_of_image_is_empty(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsImage,
        ValueCollectionInterface $values
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $attributeAsImage->getCode()->willReturn('picture');

        $values->getByCodes('picture', null, null)->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);

        $this->getImage()->shouldReturn(null);
    }
}
