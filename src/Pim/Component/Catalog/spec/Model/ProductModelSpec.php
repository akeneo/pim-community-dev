<?php

namespace spec\Pim\Component\Catalog\Model;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
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

    function it_gets_the_label_regardless_of_the_specified_scope_if_the_attribute_as_label_is_not_scopable(
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
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->toArray()->willreturn(['name-<all_channels>-fr_FR' => $nameValue]);

        $nameValue->getAttribute()->willReturn($attributeAsLabel);
        $nameValue->getScope()->willReturn(null);
        $nameValue->getLocale()->willReturn('fr_FR');
        $nameValue->getData()->willReturn('Petit outil agricole authentique');

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petit outil agricole authentique');
    }

    function it_gets_the_label_if_the_scope_is_specified_and_the_attribute_as_label_is_scopable(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $mobileNameValue,
        ValueInterface $ecommerceNameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->toArray()->willreturn([
            'name-ecommerce-fr_FR' => $ecommerceNameValue,
            'name-mobile-fr_FR' => $mobileNameValue,
        ]);

        $mobileNameValue->getAttribute()->willReturn($attributeAsLabel);
        $mobileNameValue->getScope()->willReturn('mobile');
        $mobileNameValue->getLocale()->willReturn('fr_FR');
        $mobileNameValue->getData()->willReturn('Petite pelle');

        $ecommerceNameValue->getAttribute()->willReturn($attributeAsLabel);
        $ecommerceNameValue->getScope()->willReturn('ecommerce');
        $ecommerceNameValue->getLocale()->willReturn('fr_FR');
        $ecommerceNameValue->getData()->willReturn('Petit outil agricole authentique');

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petite pelle');
    }

    function it_gets_the_code_as_label_if_there_is_no_attribute_as_label(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        ValueCollectionInterface $values
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_code_as_label_if_the_label_value_is_null(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->toArray()->willreturn([]);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_code_as_label_if_the_label_value_data_is_empty(
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
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->toArray()->willreturn(['name-<all_channels>-fr_FR' => $nameValue]);

        $nameValue->getAttribute()->willReturn($attributeAsLabel);
        $nameValue->getScope()->willReturn(null);
        $nameValue->getLocale()->willReturn('fr_FR');
        $nameValue->getData()->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_code_as_label_if_no_scope_is_specified_but_the_attribute_as_label_is_scopable(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $mobileNameValue,
        ValueInterface $ecommerceNameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->toArray()->willreturn([
            'name-ecommerce-fr_FR' => $ecommerceNameValue,
            'name-mobile-fr_FR' => $mobileNameValue,
        ]);

        $mobileNameValue->getAttribute()->willReturn($attributeAsLabel);
        $mobileNameValue->getScope()->willReturn('mobile');
        $mobileNameValue->getLocale()->willReturn('fr_FR');
        $mobileNameValue->getData()->willReturn('Petite pelle');

        $ecommerceNameValue->getAttribute()->willReturn($attributeAsLabel);
        $ecommerceNameValue->getScope()->willReturn('ecommerce');
        $ecommerceNameValue->getLocale()->willReturn('fr_FR');
        $ecommerceNameValue->getData()->willReturn('Petit outil agricole authentique');

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_label_if_no_locale_is_specified(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $nameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(false);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->toArray()->willreturn(['name-<all_channels>-fr_FR' => $nameValue]);

        $nameValue->getAttribute()->willReturn($attributeAsLabel);
        $nameValue->getScope()->willReturn(null);
        $nameValue->getLocale()->willReturn('fr_FR');
        $nameValue->getData()->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel()->shouldReturn('shovel');
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
        $attributeAsImage->isUnique()->willReturn(false);

        $values->toArray()->willreturn(['picture-<all_channels>-<all_locales>' => $pictureValue]);

        $pictureValue->getAttribute()->willReturn($attributeAsImage);
        $pictureValue->getScope()->willReturn(null);
        $pictureValue->getLocale()->willReturn(null);

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

        $values->toArray()->willreturn([]);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);

        $this->getImage()->shouldReturn(null);
    }

    function it_has_the_values_of_the_variation(ValueCollectionInterface $valueCollection)
    {
        $this->setValues($valueCollection);
        $this->getValuesForVariation()->shouldReturn($valueCollection);
    }

    function it_has_values(
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
        $values->toArray()->shouldBeLike([
            'value-<all_channels>-<all_locales>' => $value,
            'otherValue-<all_channels>-<all_locales>' => $otherValue
        ]);
    }
}
