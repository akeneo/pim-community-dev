<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\FamilyAddedToProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\FamilyOfProductChanged;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\FamilyRemovedFromProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ParentOfProductAdded;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductAddedToGroup;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductCategorized;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductDisabled;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductEnabled;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductIdentifierUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductRemovedFromGroup;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductUncategorized;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueAdded;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueDeleted;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueEdited;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

class ProductSpec extends ObjectBehavior
{
    function let()
    {
        $this->setIdentifier(ScalarValue::value('attribute_1', 'my_identifier'));
        $this->shouldHaveEventsLike([
            new ProductEnabled(),
            new ProductCreated(),
        ]);
    }

    function it_disables_the_product()
    {
        $this->setId(1);

        $this->setEnabled(false);
        $this->shouldHaveEventsLike([
            new ProductDisabled(),
        ]);
    }

    function it_can_have_a_family_added(FamilyInterface $family)
    {
        $this->setId(1);
        $family->getId()->willReturn(42);
        $family->getCode()->willReturn('clothing');

        $this->setFamily($family);
        $this->getFamily()->shouldReturn($family);
        $this->shouldHaveEventsLike([new FamilyAddedToProduct('clothing')]);
    }

    function it_can_have_its_family_changed(FamilyInterface $family1, FamilyInterface $family2)
    {
        $this->setId(1);
        $family1->getCode()->willReturn('clothing');
        $family2->getCode()->willReturn('accessories');

        $this->setFamily($family1);
        $this->shouldHaveEventsLike([new FamilyAddedToProduct('clothing')]);
        $this->setId(1);

        $this->setFamily($family2);
        $this->shouldHaveEventsLike([new FamilyOfProductChanged('clothing', 'accessories')]);
    }

    function it_can_have_its_family_removed(FamilyInterface $family)
    {
        $this->setId(1);
        $family->getCode()->willreturn('clothing');

        $this->setFamily($family);
        $this->shouldHaveEventsLike([new FamilyAddedToProduct('clothing')]);

        $this->setFamily(null);
        $this->shouldHaveEventsLike([new FamilyRemovedFromProduct('clothing')]);
    }

    function it_purges_events_when_popping_them(CategoryInterface $category1)
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

        $this->shouldHaveEventsLike([
            new ProductCategorized('category_1'),
            new ProductCategorized('category_2'),
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

        $this->shouldHaveEventsLike([new ProductUncategorized('category_1')]);
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
        $this->shouldHaveEventsLike([
            new ProductUncategorized('category_1'),
            new ProductCategorized('category_3'),
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
        $family->getCode()->willReturn('clothing');
        $attributes->contains($attribute)->willReturn(false);
        $family->getAttributes()->willReturn($attributes);
        $this->setFamily($family);
        $this->hasAttributeInfamily($attribute)->shouldReturn(false);
    }

    function it_has_attribute_in_family(AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $attributes)
    {
        $family->getCode()->willReturn('clothing');
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
        AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $familyAttributes)
    {
        $family->getCode()->willReturn('clothing');
        $familyAttributes->contains($attribute)->willReturn(true);
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
        $family->getCode()->willReturn('clothing');
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
        AttributeInterface $attributeAsLabel
    ) {
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getCode()->willReturn('clothing');
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $this->setFamily($family);
        $this->addOrReplaceValue(ScalarValue::localizableValue('name', 'Petit outil agricole authentique', 'fr_FR'));

        $this->getLabel('fr_FR')->shouldReturn('Petit outil agricole authentique');
    }

    function it_gets_the_label_regardless_of_the_specified_scope_if_the_attribute_as_label_is_not_scopable(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getCode()->willReturn('clothing');
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $this->setFamily($family);
        $this->addOrReplaceValue(ScalarValue::localizableValue('name', 'Petit outil agricole authentique', 'fr_FR'));

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petit outil agricole authentique');
    }

    function it_gets_the_label_if_the_scope_is_specified_and_the_attribute_as_label_is_scopable(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getCode()->willReturn('clothing');
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(true);

        $this->setFamily($family);
        $this->addOrReplaceValue(ScalarValue::scopableLocalizableValue('name', 'Petite pelle', 'mobile', 'fr_FR'));

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petite pelle');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_family()
    {
        $this->getLabel('fr_FR')->shouldReturn('my_identifier');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_attribute_as_label(FamilyInterface $family)
    {
        $family->getAttributeAsLabel()->willReturn(null);
        $family->getCode()->willReturn('clothing');

        $this->setFamily($family);

        $this->getLabel('fr_FR')->shouldReturn('my_identifier');
    }

    function it_gets_the_identifier_as_label_if_the_label_value_is_null(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getCode()->willReturn('clothing');
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $this->setFamily($family);

        $this->getLabel('fr_FR')->shouldReturn('my_identifier');
    }

    function it_gets_the_image_of_the_product(
        FamilyInterface $family,
        AttributeInterface $attributeAsImage
    ) {
        $attributeAsImage->getCode()->willReturn('picture');

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $family->getCode()->willReturn('clothing');

        $pictureValue = MediaValue::value('picture', new FileInfo());
        $this->setFamily($family);
        $this->addOrReplaceValue($pictureValue);

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
        $family->getCode()->willReturn('clothing');

        $this->setFamily($family);

        $this->getImage()->shouldReturn(null);
    }

    function it_gets_no_image_if_the_value_of_image_is_empty(
        FamilyInterface $family,
        AttributeInterface $attributeAsImage
    ) {
        $attributeAsImage->getCode()->willReturn('picture');

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $family->getCode()->willReturn('clothing');

        $this->setFamily($family);

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
        $this->shouldHaveEventsLike([
            new ParentOfProductAdded('parent_code')
        ]);
    }

    function it_has_the_values_of_the_variation()
    {
        $valueCollection = new WriteValueCollection(
            [
                ScalarValue::value('toto', 'titi'),
            ]
        );
        $this->setValues($valueCollection);

        $this->getValuesForVariation()->shouldBeLike($valueCollection);
    }

    function it_has_values_when_it_is_not_variant() {
        $valueCollection = new WriteValueCollection([]);
        $this->setValues($valueCollection);
        $this->setParent(null);

        $this->getValues()->shouldBeLike($valueCollection);
    }

    function it_has_values_of_its_parent_when_it_is_variant()
    {
        $productModel = new ProductModel();
        $productModel->setCode('parent');
        $productModelValue = ScalarValue::value('pm_attr', 'data');
        $productModel->setValues(new WriteValueCollection([
            $productModelValue
        ]));

        $this->setParent($productModel);

        $productValue = ScalarValue::value('product_attr', 'some_other_data');
        $this->setValues(new WriteValueCollection([
            $productValue
        ]));

        $values = $this->getValues();
        $values->toArray()->shouldBeLike([
            'product_attr-<all_channels>-<all_locales>' => $productValue,
            'pm_attr-<all_channels>-<all_locales>' => $productModelValue,
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

        $this->shouldHaveEventsLike([
            new ProductAddedToGroup('groupA'),
            new ProductAddedToGroup('groupB'),
        ]);

        $this->setGroups(new ArrayCollection([$groupC->getWrappedObject(), $groupA->getWrappedObject()]));
        $this->shouldHaveEventsLike([
            new ProductRemovedFromGroup('groupB'),
            new ProductAddedToGroup('groupC'),
        ]);
    }

    function it_can_be_added_to_a_group(GroupInterface $promotions)
    {
        $this->setId(42);
        $promotions->getCode()->willReturn('promotions');

        $this->addGroup($promotions);

        $this->shouldHaveEventsLike([new ProductAddedToGroup('promotions')]);
    }

    function it_can_be_removed_from_a_group(GroupInterface $promotions)
    {
        $promotions->getCode()->willReturn('promotions');
        $this->setId(42);
        $this->setGroups(new ArrayCollection([$promotions->getWrappedObject()]));
        $this->popEvents()->shouldHaveCount(1);

        $this->removeGroup($promotions);
        $this->shouldHaveEventsLike([
            new ProductRemovedFromGroup('promotions'),
        ]);
    }

    function it_does_not_pop_event_if_this_is_a_new_object()
    {
        $this->setIdentifier(ScalarValue::value('attribute_1', 'my_identifier'));
        $this->shouldHaveEventsLike([
            new ProductCreated(),
        ]);
    }

    function it_pops_an_event_if_identifier_change()
    {
        $this->setIdentifier(ScalarValue::value('attribute_1', 'my_identifier'));
        $this->setIdentifier(ScalarValue::value('attribute_1', 'my_identifier_2'));

        $this->shouldHaveEventsLike([
            new ProductIdentifierUpdated('my_identifier'),
            new ProductCreated(),
        ]);
    }

    function it_adds_a_value()
    {
        $this->setId(1);

        $this->addOrReplaceValue(ScalarValue::value('attribute_code', 'data'));

        $this->shouldHaveEventsLike([
            new ValueAdded('attribute_code', null, null)
        ]);
    }

    function it_replaces_an_identical_value()
    {
        $this->setId(1);
        $this->setValues(new WriteValueCollection([ScalarValue::value('attribute_code', 'data')]));
        $this->popEvents();

        $this->addOrReplaceValue(ScalarValue::value('attribute_code', 'data'));

        $this->shouldHaveEventsLike([]);
    }

    function it_replaces_a_value()
    {
        $this->setId(1);
        $this->setValues(new WriteValueCollection([ScalarValue::value('attribute_code', 'former_data')]));
        $this->popEvents();

        $this->addOrReplaceValue(ScalarValue::value('attribute_code', 'data'));
        $this->shouldHaveEventsLike([
            new ValueEdited('attribute_code', null, null)
        ]);
    }

    function it_removes_a_value()
    {
        $value = ScalarValue::value('attribute_code', 'former_data');

        $this->setId(1);
        $this->setValues(new WriteValueCollection([$value]));
        $this->popEvents();

        $this->removeValue($value);
        $this->shouldHaveEventsLike([
            new ValueDeleted('attribute_code', null, null)
        ]);
    }

    function it_does_not_remove_non_existent_value()
    {
        $this->setId(1);
        $this->setValues(new WriteValueCollection([]));
        $this->popEvents();

        $this->removeValue(ScalarValue::value('attribute_code', 'former_data'));
        $this->shouldHaveEventsLike([]);
    }

    function it_adds_several_values()
    {
        $this->setId(1);
        $values = new WriteValueCollection([
            ScalarValue::value('attribute_1', 'my_identifier'),
            ScalarValue::value('color', 'red'),
            ScalarValue::value('name', 'Jon Snow'),
        ]);

        $this->setValues($values);
        $this->shouldHaveEventsLike([
            new ValueAdded('color', null, null),
            new ValueAdded('name', null, null),
        ]);
    }

    function it_does_not_replace_identical_values()
    {
        $this->setId(42);
        $this->setValues(new WriteValueCollection([
            ScalarValue::scopableLocalizableValue('description', 'saucisson', 'mobile', 'fr_FR'),
        ]));
        $this->popEvents();

        $this->setValues(
            new WriteValueCollection(
                [
                    ScalarValue::scopableLocalizableValue('description', 'saucisson', 'mobile', 'fr_FR'),
                ]
            )
        );
        $this->popEvents()->shouldReturn([]);
    }

    function it_adds_or_replaces_or_removes_several_values()
    {
        $this->setId(1);
        $this->setValues(
            new WriteValueCollection(
                [
                    ScalarValue::localizableValue('color', 'red', 'en_US'),
                    ScalarValue::value('name', 'Jon Snow'),
                    ScalarValue::value('size', 'XL'),
                ]
            ));
        $this->popEvents();

        $this->setValues(
            new WriteValueCollection(
                [
                    ScalarValue::localizableValue('color', 'rouge', 'fr_FR'),
                    ScalarValue::value('name', 'Aegon Targaryen'),
                    ScalarValue::value('size', 'XL'),
                ]
            ));

        $this->shouldHaveEventsLike([
            new ValueDeleted('color', 'en_US', null),
            new ValueAdded('color', 'fr_FR', null),
            new ValueEdited('name', null, null),
        ]);
    }

    public function getMatchers(): array
    {
        return [
            'haveEventsLike' => function($subject, $expectedEvents) {
                foreach ($expectedEvents as $event) {
                    $event->setProductIdentifier($subject->getIdentifier());
                }

                if ($subject->popEvents() != $expectedEvents) {
                    throw new FailureException('Expected events to not match actual ones');
                }

                return true;
            }
        ];
    }
}
