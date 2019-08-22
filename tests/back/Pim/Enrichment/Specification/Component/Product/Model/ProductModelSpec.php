<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

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
        WriteValueCollection $values,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $this->setValues($values);

        $attribute->getCode()->willReturn('foobar');
        $attribute->isUnique()->willReturn(false);

        $value->getAttributeCode()->willReturn('foobar');
        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);

        $values->add($value)->shouldBeCalled();

        $this->addValue($value)->shouldReturn($this);
    }

    function it_removes_a_value(
        WriteValueCollection $values,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $this->setValues($values);

        $value->getAttributeCode()->willReturn('foobar');
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
        WriteValueCollection $values,
        ValueInterface $nameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willreturn($nameValue);

        $nameValue->getAttributeCode()->willReturn('name');
        $nameValue->getScopeCode()->willReturn(null);
        $nameValue->getLocaleCode()->willReturn('fr_FR');
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
        WriteValueCollection $values,
        ValueInterface $mobileNameValue,
        ValueInterface $ecommerceNameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->getByCodes('name', 'mobile', 'fr_FR')->willreturn($mobileNameValue);

        $mobileNameValue->getAttributeCode()->willReturn('name');
        $mobileNameValue->getScopeCode()->willReturn('mobile');
        $mobileNameValue->getLocaleCode()->willReturn('fr_FR');
        $mobileNameValue->getData()->willReturn('Petite pelle');

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petite pelle');
    }

    function it_gets_the_code_as_label_if_there_is_no_attribute_as_label(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        WriteValueCollection $values
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
        WriteValueCollection $values
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willreturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_code_as_label_if_the_label_value_data_is_empty(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        WriteValueCollection $values,
        ValueInterface $nameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);

        $nameValue->getAttributeCode()->willReturn('name');
        $nameValue->getScopeCode()->willReturn(null);
        $nameValue->getLocaleCode()->willReturn('fr_FR');
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
        WriteValueCollection $values,
        ValueInterface $mobileNameValue,
        ValueInterface $ecommerceNameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->getByCodes('name', null, 'fr_FR')->willreturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_label_if_no_locale_is_specified(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        WriteValueCollection $values,
        ValueInterface $nameValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(false);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, null)->willreturn($nameValue);

        $nameValue->getAttributeCode()->willReturn('name');
        $nameValue->getScopeCode()->willReturn(null);
        $nameValue->getLocaleCode()->willReturn('fr_FR');
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
        WriteValueCollection $values,
        ValueInterface $pictureValue
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $attributeAsImage->getCode()->willReturn('picture');
        $attributeAsImage->isUnique()->willReturn(false);

        $values->getByCodes('picture', null, null)->willReturn($pictureValue);

        $pictureValue->getAttributeCode()->willReturn('picture');
        $pictureValue->getScopeCode()->willReturn(null);
        $pictureValue->getLocaleCode()->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);

        $this->getImage()->shouldReturn($pictureValue);
    }

    function it_gets_no_image_if_there_is_no_attribute_as_image(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        WriteValueCollection $values
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
        WriteValueCollection $values
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $attributeAsImage->getCode()->willReturn('picture');

        $values->getByCodes('picture', null, null)->willreturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);

        $this->getImage()->shouldReturn(null);
    }

    function it_has_the_values_of_the_variation(WriteValueCollection $valueCollection)
    {
        $this->setValues($valueCollection);
        $this->getValuesForVariation()->shouldReturn($valueCollection);
    }

    function it_has_values(
        WriteValueCollection $valueCollection,
        ProductModelInterface $productModel,
        WriteValueCollection $parentValuesCollection,
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
        $value->getAttributeCode()->willReturn('value');
        $value->getScopeCode()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);

        $otherValueAttribute->getCode()->willReturn('otherValue');
        $otherValueAttribute->isUnique()->willReturn(false);
        $otherValue->getAttributeCode()->willReturn('otherValue');
        $otherValue->getScopeCode()->willReturn(null);
        $otherValue->getLocaleCode()->willReturn(null);

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

    function it_gets_label_when_casting_object_as_string(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        WriteValueCollection $values
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, null)->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->__toString()->shouldReturn('shovel');
    }

    function it_saves_empty_raw_values()
    {
        $this->setRawValues([]);
        $this->getRawValues()->shouldReturn([]);
    }
}
