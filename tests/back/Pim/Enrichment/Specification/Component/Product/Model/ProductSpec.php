<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ParentOfProductAdded;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductAddedToGroup;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductCategorized;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductDisabled;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductEnabled;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductIdentifierUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductRemovedFromGroup;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductUncategorized;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class ProductSpec extends ObjectBehavior
{
    function let()
    {
        $this->setIdentifier(ScalarValue::value('attribute_1', 'my_identifier'));
        $this->popEvents()->shouldBeLike([
            new ProductEnabled(null), // TODO: fix null
            new ProductCreated('my_identifier'),
        ]);

        $this->popEvents();
    }

    function it_disables_the_product()
    {
        $this->setId(1);

        $this->setEnabled(false);
        $this->popEvents()->shouldBeLike([
            new ProductDisabled('my_identifier'),
        ]);
    }

    function it_has_family(FamilyInterface $family)
    {
        $family->getId()->willReturn(42);
        $this->setFamily($family);
        $this->getFamily()->shouldReturn($family);
        $this->getFamilyId()->shouldReturn(42);
    }

    function it_purge_events_when_popping_them(CategoryInterface $category1)
    {
        $this->setId(1);

        $category1->getCode()->willReturn('category_1');
        $this->addCategory($category1);

        $this->popEvents()->shouldHaveCount(1);
        $this->popEvents()->shouldHaveCount(0);
    }

    function it_categorized_the_product(CategoryInterface $category1, CategoryInterface $category2)
    {
        $this->setId(1);
        $this->setIdentifier(ScalarValue::value('attribute_1', 'my_identifier'));

        $category1->getCode()->willReturn('category_1');
        $category2->getCode()->willReturn('category_2');

        $this->addCategory($category1);
        $this->getCategories()->shouldHaveCount(1);
        $this->addCategory($category2);
        $this->getCategories()->shouldHaveCount(2);

        $this->popEvents()->shouldBeLike([
            new ProductCategorized('my_identifier', 'category_1'),
            new ProductCategorized('my_identifier', 'category_2'),
        ]);
    }

    function it_uncategorized_the_product(CategoryInterface $category1)
    {
        $this->setId(1);

        $category1->getCode()->willReturn('category_1');

        $this->addCategory($category1);
        $this->popEvents();

        $this->removeCategory($category1);
        $this->getCategories()->shouldHaveCount(0);

        $this->popEvents()->shouldBeLike([new ProductUncategorized('my_identifier', 'category_1')]);
    }

    function it_is_categorized_or_uncategorized_the_product_by_replacing_all_categories(
        CategoryInterface $category1,
        CategoryInterface $category2,
        CategoryInterface $category3
    ) {
        $this->setId(1);

        $category1->getCode()->willReturn('category_1');
        $category2->getCode()->willReturn('category_2');
        $category3->getCode()->willReturn('category_3');

        $this->addCategory($category1);
        $this->addCategory($category2);
        $this->popEvents();

        $this->setCategories(new ArrayCollection([$category2->getWrappedObject(), $category3->getWrappedObject()]));
        $this->popEvents()->shouldBeLike([
            new ProductUncategorized('my_identifier', 'category_1'),
            new ProductCategorized('my_identifier', 'category_3'),
        ]);
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

    function it_has_not_attribute_in_family(AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $attributes)
    {
        $attributes->contains($attribute)->willReturn(false);
        $family->getId()->willReturn(42);
        $family->getAttributes()->willReturn($attributes);
        $this->setFamily($family);
        $this->hasAttributeInfamily($attribute)->shouldReturn(false);
    }

    function it_has_attribute_in_family(AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $attributes)
    {
        $attributes->contains($attribute)->willReturn(true);
        $family->getId()->willReturn(42);
        $family->getAttributes()->willReturn($attributes);
        $this->setFamily($family);
        $this->hasAttributeInfamily($attribute)->shouldReturn(true);
    }

    function it_is_not_attribute_editable_without_family(AttributeInterface $attribute)
    {
        $this->isAttributeEditable($attribute)->shouldReturn(false);
    }

    function it_is_attribute_editable_with_family_containing_attribute(
        AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $familyAttributes)
    {
        $familyAttributes->contains($attribute)->willReturn(true);
        $family->getId()->willReturn(42);
        $family->getAttributes()->willReturn($familyAttributes);
        $this->setFamily($family);

        $this->isAttributeEditable($attribute)->shouldReturn(true);
    }

    function it_is_not_attribute_removable_if_attribute_is_an_identifier(AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $familyAttributes)
    {
        $attribute->getType()->willReturn(AttributeTypes::IDENTIFIER);

        $this->isAttributeRemovable($attribute)->shouldReturn(false);
    }

    function it_is_not_attribute_removable_with_family_containing_attribute(
        AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $familyAttributes)
    {
        $familyAttributes->contains($attribute)->willReturn(true);
        $family->getId()->willReturn(42);
        $family->getAttributes()->willReturn($familyAttributes);

        $this->setFamily($family);
        $this->isAttributeRemovable($attribute)->shouldReturn(false);
    }

    function it_is_attribute_removable(AttributeInterface $attribute)
    {
        $this->isAttributeRemovable($attribute)->shouldReturn(true);
    }

    function it_gets_the_label_of_the_product_without_specified_scope(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        WriteValueCollection $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);

        $values->removeByAttributeCode('name')->shouldBeCalled();
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
        WriteValueCollection $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);
        $values->removeByAttributeCode('name')->shouldBeCalled();
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
        WriteValueCollection $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->getByCodes('name', 'mobile', 'fr_FR')->willReturn($nameValue);
        $values->removeByAttributeCode('name')->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $nameValue->getData()->willReturn('Petite pelle');

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petite pelle');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_family(
        AttributeInterface $attributeAsLabel,
        WriteValueCollection $values,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $attributeAsLabel->getCode()->willReturn('name');
        $values->removeByAttributeCode('name')->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $this->setFamily(null);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_attribute_as_label(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        WriteValueCollection $values,
        ValueInterface $identifier
    ) {
        $family->getAttributeAsLabel()->willReturn(null);
        $family->getId()->willReturn(42);

        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $attributeAsLabel->getCode()->willReturn('name');
        $values->removeByAttributeCode('name')->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_the_label_value_is_null(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        WriteValueCollection $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->removeByAttributeCode('name')->shouldBeCalled();
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
        WriteValueCollection $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);
        $values->removeByAttributeCode('name')->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $nameValue->getData()->willReturn(null);

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_image_of_the_product(
        FamilyInterface $family,
        AttributeInterface $attributeAsImage,
        WriteValueCollection $values,
        ValueInterface $pictureValue
    ) {
        $attributeAsImage->getCode()->willReturn('picture');

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $family->getId()->willReturn(42);

        $values->getByCodes('picture', null, null)->willReturn($pictureValue);

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
        $family->getId()->willReturn(42);

        $this->setFamily($family);

        $this->getImage()->shouldReturn(null);
    }

    function it_gets_no_image_if_the_value_of_image_is_empty(
        FamilyInterface $family,
        AttributeInterface $attributeAsImage,
        WriteValueCollection $values
    ) {
        $attributeAsImage->getCode()->willReturn('picture');

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $family->getId()->willReturn(42);

        $values->getByCodes('picture', null, null)->willReturn(null);

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
        $this->setId(1);

        $parent->getCode()->willReturn('parent_code');
        $this->setParent($parent);
        $this->isVariant()->shouldReturn(true);
        $this->popEvents()->shouldBeLike([
            new ParentOfProductAdded('my_identifier', 'parent_code')
        ]);
    }

    function it_has_the_values_of_the_variation(
        WriteValueCollection $valueCollection
    ) {
        $this->setValues($valueCollection);

        $this->getValuesForVariation()->shouldBeLike($valueCollection);
    }

    function it_has_values_when_it_is_not_variant(
        WriteValueCollection $valueCollection
    ) {
        $this->setValues($valueCollection);
        $this->setParent(null);

        $this->getValues()->shouldBeLike($valueCollection);
    }

    function it_has_values_of_its_parent_when_it_is_variant(
        WriteValueCollection $valueCollection,
        ProductModelInterface $productModel,
        WriteValueCollection $parentValuesCollection,
        \Iterator $iterator,
        ValueInterface $value,
        AttributeInterface $valueAttribute,
        ValueInterface $otherValue,
        AttributeInterface $otherValueAttribute
    ) {
        $productModel->getCode()->willReturn('product_model_code');
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

    function it_has_a_variation_level(ProductModelInterface $productModel)
    {
        $productModel->getCode()->willReturn('product_model_code');

        $this->setParent($productModel);
        $productModel->getVariationLevel()->willReturn(7);
        $this->getVariationLevel()->shouldReturn(8);
    }

    function it_has_a_product_model(ProductModelInterface $productModel)
    {
        $productModel->getCode()->willReturn('product_model_code');

        $this->setParent($productModel);
        $this->getParent()->shouldReturn($productModel);
    }

    function it_has_a_family_variant(FamilyVariantInterface $familyVariant)
    {
        $this->setFamilyVariant($familyVariant);
        $this->getFamilyVariant()->shouldReturn($familyVariant);
    }

    function it_can_set_groups(GroupInterface $groupA, GroupInterface $groupB, GroupInterface $groupC)
    {
        $this->setId(42);
        $groupA->getCode()->willReturn('groupA');
        $groupB->getCode()->willReturn('groupB');
        $groupC->getCode()->willReturn('groupC');

        $this->setGroups(new ArrayCollection([$groupA->getWrappedObject(), $groupB->getWrappedObject()]));

        $this->popEvents()->shouldBeLike([
            new ProductAddedToGroup('my_identifier', 'groupA'),
            new ProductAddedToGroup('my_identifier', 'groupB'),
        ]);

        $this->setGroups(new ArrayCollection([$groupC->getWrappedObject(), $groupA->getWrappedObject()]));
        $this->popEvents()->shouldBeLike([
            new ProductRemovedFromGroup('my_identifier', 'groupB'),
            new ProductAddedToGroup('my_identifier', 'groupC'),
        ]);
    }

    function it_can_be_added_to_a_group(GroupInterface $promotions)
    {
        $this->setId(42);
        $promotions->getCode()->willReturn('promotions');

        $promotions->addProduct($this)->shouldBeCalled();
        $this->addGroup($promotions);

        $this->popEvents()->shouldBeLike([new ProductAddedToGroup('my_identifier', 'promotions')]);
    }

    function it_can_be_removed_from_a_group(GroupInterface $promotions)
    {
        $promotions->getCode()->willReturn('promotions');
        $this->setId(42);
        $this->setGroups(new ArrayCollection([$promotions->getWrappedObject()]));
        $this->popEvents()->shouldHaveCount(1);

        $this->removeGroup($promotions);
        $this->popEvents()->shouldBeLike([
            new ProductRemovedFromGroup('my_identifier', 'promotions'),
        ]);
    }

    function it_does_not_pop_event_if_this_is_a_new_object()
    {
        $this->setIdentifier(ScalarValue::value('attribute_1', 'my_identifier'));
        $this->popEvents()->shouldBeLike([
            new ProductCreated('my_identifier'),
        ]);
    }

    function it_pops_an_event_if_identifier_change()
    {
        $this->setIdentifier(ScalarValue::value('attribute_1', 'my_identifier'));
        $this->setIdentifier(ScalarValue::value('attribute_1', 'my_identifier_2'));

        $this->popEvents()->shouldBeLike([
            new ProductIdentifierUpdated('my_identifier_2', 'my_identifier'),
            new ProductCreated('my_identifier_2'),
        ]);
    }
}
