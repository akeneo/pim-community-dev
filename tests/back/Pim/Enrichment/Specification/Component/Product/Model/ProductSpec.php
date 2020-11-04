<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;

class ProductSpec extends ObjectBehavior
{
    function it_has_family(FamilyInterface $family)
    {
        $family->getId()->willReturn(42);
        $this->setFamily($family);
        $this->getFamily()->shouldReturn($family);
        $this->getFamilyId()->shouldReturn(42);
    }

    function it_belongs_to_categories(CategoryInterface $category1, CategoryInterface $category2)
    {
        $this->addCategory($category1);
        $this->getCategories()->shouldHaveCount(1);
        $this->addCategory($category2);
        $this->getCategories()->shouldHaveCount(2);
    }

    function it_returns_association_from_an_association_type(
        ProductAssociation $assoc1,
        ProductAssociation $assoc2,
        AssociationTypeInterface $assocType1,
        AssociationTypeInterface $assocType2,
        Collection $associations,
        \Iterator $associationsIterator
    ) {
        $associations->getIterator()->willReturn($associationsIterator);
        $associationsIterator->current()->willReturn($assoc1, $assoc2);
        $associationsIterator->rewind()->shouldBeCalled();
        $associationsIterator->valid()->willReturn(true, true, false);

        $assoc1->getAssociationType()->willReturn($assocType1);
        $assoc2->getAssociationType()->willReturn($assocType2);

        $this->setAssociations($associations);
        $this->getAssociationForType($assocType1)->shouldReturn($assoc1);
    }

    function it_returns_association_from_an_association_type_code(
        ProductAssociation $assoc1,
        ProductAssociation $assoc2,
        AssociationTypeInterface $assocType1,
        AssociationTypeInterface $assocType2,
        Collection $associations,
        \Iterator $associationsIterator
    ) {
        $associations->getIterator()->willReturn($associationsIterator);
        $associationsIterator->current()->willReturn($assoc1, $assoc2);
        $associationsIterator->next()->shouldBeCalled();
        $associationsIterator->rewind()->shouldBeCalled();
        $associationsIterator->valid()->willReturn(true, true, false);

        $assocType1->getCode()->willReturn('ASSOC_TYPE_1');
        $assocType2->getCode()->willReturn('ASSOC_TYPE_2');
        $assoc1->getAssociationType()->willReturn($assocType1);
        $assoc2->getAssociationType()->willReturn($assocType2);

        $this->setAssociations($associations);
        $this->getAssociationForTypeCode('ASSOC_TYPE_2')->shouldReturn($assoc2);
    }

    function it_returns_null_when_i_try_to_get_an_association_with_an_empty_collection(
        AssociationTypeInterface $assocType1,
        Collection $associations,
        \Iterator $associationsIterator
    ) {
        $associations->getIterator()->willReturn($associationsIterator);

        $this->setAssociations($associations);
        $this->getAssociationForType($assocType1)->shouldReturn(null);
    }

    function it_has_not_attribute_in_family_without_family(AttributeInterface $attribute)
    {
        $this->hasAttributeInfamily($attribute)->shouldReturn(false);
    }

    function it_has_not_attribute_in_family(
        AttributeInterface $attribute,
        FamilyInterface $family,
        ArrayCollection $attributes
    ) {
        $attributes->contains($attribute)->willReturn(false);
        $family->getAttributes()->willReturn($attributes);
        $this->setFamily($family);
        $this->hasAttributeInfamily($attribute)->shouldReturn(false);
    }

    function it_has_attribute_in_family(
        AttributeInterface $attribute,
        FamilyInterface $family,
        ArrayCollection $attributes
    ) {
        $attributes->contains($attribute)->willReturn(true);
        $family->getAttributes()->willReturn($attributes);
        $this->setFamily($family);
        $this->hasAttributeInfamily($attribute)->shouldReturn(true);
    }

    function it_is_not_attribute_editable_without_family(AttributeInterface $attribute)
    {
        $this->isAttributeEditable($attribute)->shouldReturn(false);
    }

    function it_is_attribute_editable_with_family_containing_attribute(
        AttributeInterface $attribute,
        FamilyInterface $family,
        ArrayCollection $familyAttributes
    ) {
        $familyAttributes->contains($attribute)->willReturn(true);
        $family->getAttributes()->willReturn($familyAttributes);
        $this->setFamily($family);

        $this->isAttributeEditable($attribute)->shouldReturn(true);
    }

    function it_is_not_attribute_removable_if_attribute_is_an_identifier(
        AttributeInterface $attribute,
        FamilyInterface $family,
        ArrayCollection $familyAttributes
    ) {
        $attribute->getType()->willReturn(AttributeTypes::IDENTIFIER);

        $this->isAttributeRemovable($attribute)->shouldReturn(false);
    }

    function it_is_not_attribute_removable_with_family_containing_attribute(
        AttributeInterface $attribute,
        FamilyInterface $family,
        ArrayCollection $familyAttributes
    ) {
        $familyAttributes->contains($attribute)->willReturn(true);
        $family->getAttributes()->willReturn($familyAttributes);

        $this->setFamily($family);
        $this->isAttributeRemovable($attribute)->shouldReturn(false);
    }

    function it_is_attribute_removable(AttributeInterface $attribute)
    {
        $this->isAttributeRemovable($attribute)->shouldReturn(true);
    }

    function it_gets_the_label_regardless_of_the_specified_scope_if_the_attribute_as_label_is_not_scopable(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values = new WriteValueCollection(
            [
                ScalarValue::value('sku', 'shovel'),
                ScalarValue::localizableValue('name', 'Petit outil agricole authentique', 'fr_FR'),
            ]
        );

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petit outil agricole authentique');
    }

    function it_gets_the_label_if_the_scope_is_specified_and_the_attribute_as_label_is_scopable(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values = new WriteValueCollection(
            [
                ScalarValue::value('sku', 'shovel'),
                ScalarValue::scopableLocalizableValue('name', 'Petite pelle', 'mobile', 'fr_FR'),
            ]
        );

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petite pelle');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_family(
        AttributeInterface $attributeAsLabel,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $attributeAsLabel->getCode()->willReturn('name');

        $this->setFamily(null);
        $this->setValues(new WriteValueCollection());
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_attribute_as_label(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueInterface $identifier
    ) {
        $family->getAttributeAsLabel()->willReturn(null);

        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $attributeAsLabel->getCode()->willReturn('name');

        $this->setFamily($family);
        $this->setValues(new WriteValueCollection());
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_the_label_value_is_null(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $this->setFamily($family);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_image_of_the_product(
        FamilyInterface $family,
        AttributeInterface $attributeAsImage,
        ValueInterface $pictureValue
    ) {
        $attributeAsImage->getCode()->willReturn('picture');
        $family->getAttributeAsImage()->willReturn($attributeAsImage);

        $pictureValue->getAttributeCode()->willReturn('picture');
        $pictureValue->getScopeCode()->willReturn(null);
        $pictureValue->getLocaleCode()->willReturn(null);

        $values = new WriteValueCollection(
            [
                $pictureValue->getWrappedObject(),
            ]
        );

        $this->setFamily($family);
        $this->setValues($values);

        $this->getImage()->shouldReturn($pictureValue);
    }

    function it_gets_no_image_if_there_is_no_family()
    {
        $this->setFamily(null);
        $this->getImage()->shouldReturn(null);
    }

    function it_gets_no_image_if_there_is_no_attribute_as_image(
        FamilyInterface $family
    ) {
        $family->getAttributeAsImage()->willReturn(null);

        $this->setFamily($family);

        $this->getImage()->shouldReturn(null);
    }

    function it_gets_no_image_if_the_value_of_image_is_empty(
        FamilyInterface $family,
        AttributeInterface $attributeAsImage
    ) {
        $attributeAsImage->getCode()->willReturn('picture');

        $family->getAttributeAsImage()->willReturn($attributeAsImage);

        $values = new WriteValueCollection();

        $this->setFamily($family);
        $this->setValues($values);

        $this->getImage()->shouldReturn(null);
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

    function it_has_the_values_of_the_variation()
    {
        $valueCollection = new WriteValueCollection();
        $this->setValues($valueCollection);

        $this->getValuesForVariation()->shouldBeLike($valueCollection);
    }

    function it_has_values_when_it_is_not_variant()
    {
        $valueCollection = new WriteValueCollection();
        $this->setValues($valueCollection);
        $this->setParent(null);

        $this->getValues()->shouldBeLike($valueCollection);
    }

    function it_has_values_of_its_parent_when_it_is_variant(
        ProductModelInterface $productModel,
        \Iterator $iterator,
        ValueInterface $value,
        ValueInterface $otherValue
    ) {
        $value->getAttributeCode()->willReturn('value');
        $value->getScopeCode()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);

        $valueCollection = new WriteValueCollection([$value->getWrappedObject()]);
        $this->setValues($valueCollection);
        $this->setParent($productModel);

        $otherValue->getAttributeCode()->willReturn('otherValue');
        $otherValue->getScopeCode()->willReturn(null);
        $otherValue->getLocaleCode()->willReturn(null);
        $parentValuesCollection = new WriteValueCollection([$otherValue->getWrappedObject()]);

        $productModel->getParent()->willReturn(null);
        $productModel->getValuesForVariation()->willReturn($parentValuesCollection);

        $values = $this->getValues();
        $values->toArray()->shouldBeLike(
            [
                'value-<all_channels>-<all_locales>' => $value,
                'otherValue-<all_channels>-<all_locales>' => $otherValue,
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

    function it_is_updated_at_instantiation()
    {
        $this->beConstructedWith([]);
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_can_reset_its_updated_state()
    {
        $this->cleanup();
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_a_category_is_added(CategoryInterface $category)
    {
        $this->cleanup();

        $this->addCategory($category);
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_an_already_existing_category_is_added(
        CategoryInterface $category
    ) {
        $this->setCategories(new ArrayCollection([$category->getWrappedObject()]));
        $this->cleanup();

        $this->addCategory($category);
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_a_category_is_removed(CategoryInterface $category)
    {
        $this->setCategories(new ArrayCollection([$category->getWrappedObject()]));
        $this->cleanup();

        $this->removeCategory($category);
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_a_non_existing_category_is_removed(CategoryInterface $category)
    {
        $this->cleanup();

        $this->removeCategory($category);
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_setting_or_removing_categories(
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $this->setCategories(new ArrayCollection([$category1->getWrappedObject()]));
        $this->cleanup();

        $this->setCategories(new ArrayCollection([$category2->getWrappedObject()]));
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_updated_when_setting_the_same_categories(
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $this->setCategories(new ArrayCollection([$category1->getWrappedObject(), $category2->getWrappedObject()]));
        $this->cleanup();

        $this->setCategories(new ArrayCollection([$category2->getWrappedObject(), $category1->getWrappedObject()]));
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_a_group_is_added(GroupInterface $group)
    {
        $this->cleanup();

        $this->addGroup($group);
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_an_existing_group_is_added(GroupInterface $group)
    {
        $this->setGroups(new ArrayCollection([$group->getWrappedObject()]));
        $this->cleanup();

        $this->addGroup($group);
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_a_group_is_removed(GroupInterface $group)
    {
        $this->setGroups(new ArrayCollection([$group->getWrappedObject()]));
        $this->cleanup();

        $this->removeGroup($group);
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_a_non_existing_group_is_removed(GroupInterface $group)
    {
        $this->cleanup();

        $this->removeGroup($group);
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_setting_or_removing_groups(GroupInterface $group1, GroupInterface $group2)
    {
        $this->setGroups(new ArrayCollection([$group1->getWrappedObject()]));
        $this->cleanup();

        $this->setGroups(new ArrayCollection([$group2->getWrappedObject()]));
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_setting_the_same_groups(GroupInterface $group1, GroupInterface $group2)
    {
        $this->setGroups(new ArrayCollection([$group1->getWrappedObject(), $group2->getWrappedObject()]));
        $this->cleanup();

        $this->setGroups(new ArrayCollection([$group2->getWrappedObject(), $group1->getWrappedObject()]));
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_changing_the_identifier()
    {
        $this->setIdentifier('foo');
        $this->cleanup();

        $this->setIdentifier('baz');
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_setting_the_same_identifier()
    {
        $this->setIdentifier('foo');
        $this->cleanup();

        $this->setIdentifier('foo');
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_updating_the_status()
    {
        $this->cleanup();
        $this->setEnabled(false);
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_the_status_is_not_updated()
    {
        $this->cleanup();
        $this->setEnabled(true);
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_updating_the_parent_model(
        ProductModelInterface $productModel,
        ProductModelInterface $otherProductModel
    ) {
        $this->setParent($productModel);
        $this->cleanup();

        $this->setParent($otherProductModel);
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_setting_the_same_parent_model(ProductModelInterface $parent)
    {
        $this->setParent($parent);
        $this->cleanup();

        $this->setParent($parent);
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_changing_the_family_variant(
        FamilyVariantInterface $familyVariant,
        FamilyVariantInterface $otherFamilyVariant
    ) {
        $this->setFamilyVariant($familyVariant);
        $this->cleanup();

        $this->setFamilyVariant($otherFamilyVariant);
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_setting_the_same_family_variant(
        FamilyVariantInterface $familyVariant
    ) {
        $this->setFamilyVariant($familyVariant);
        $this->cleanup();

        $this->setFamilyVariant($familyVariant);
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_a_value_is_added()
    {
        $this->cleanup();
        $this->addValue(ScalarValue::value('name', 'My great product'));

        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_a_value_fails_to_be_added()
    {
        $this->addValue(ScalarValue::value('name', 'My great product'));
        $this->cleanup();

        $this->addValue(ScalarValue::value('name', 'Another name'));
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_a_value_is_removed()
    {
        $value = ScalarValue::value('name', 'My great product');
        $this->addValue($value);
        $this->cleanup();

        $this->removeValue($value);
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_a_value_fails_to_be_removed()
    {
        $this->cleanup();
        $this->removeValue(ScalarValue::value('name', 'My great product'));

        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_setting_new_values()
    {
        $this->cleanup();
        $this->setValues(
            new WriteValueCollection(
                [
                    ScalarValue::value('name', 'My great product'),
                ]
            )
        );

        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_updated_when_setting_a_new_value()
    {
        $this->addValue(ScalarValue::value('name', 'My great product'));
        $this->cleanup();

        $this->setValues(
            new WriteValueCollection(
                [
                    ScalarValue::value('name', 'Another name for my great product'),
                ]
            )
        );
        $this->wasUpdated()->shouldReturn(true);
    }

    function it_is_not_updated_when_setting_the_same_values()
    {
        $this->addValue(ScalarValue::value('name', 'My great product'));
        $this->addValue(OptionValue::scopableLocalizableValue('color', 'red', 'ecommerce', 'en_US'));
        $this->cleanup();

        $this->setValues(
            new WriteValueCollection(
                [
                    ScalarValue::value('name', 'My great product'),
                    OptionValue::scopableLocalizableValue('color', 'red', 'ecommerce', 'en_US'),
                ]
            )
        );
        $this->wasUpdated()->shouldReturn(false);
    }

    function it_is_updated_when_removing_a_value()
    {
        $this->addValue(ScalarValue::value('name', 'My great product'));
        $this->addValue(OptionValue::scopableLocalizableValue('color', 'red', 'ecommerce', 'en_US'));
        $this->cleanup();

        $this->setValues(
            new WriteValueCollection(
                [
                    OptionValue::scopableLocalizableValue('color', 'red', 'ecommerce', 'en_US'),
                ]
            )
        );
        $this->wasUpdated()->shouldReturn(true);
    }
}
