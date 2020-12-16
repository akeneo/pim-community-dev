<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductSpec extends ObjectBehavior
{
    function it_has_family(FamilyInterface $family)
    {
        $family->getId()->willReturn(42);
        $family->getCode()->willReturn('clothes');
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
        $family->getCode()->willReturn('furniture');
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
        $family->getCode()->willReturn('furniture');
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
        $family->getCode()->willReturn('furniture');
        $this->setFamily($family);

        $this->isAttributeEditable($attribute)->shouldReturn(true);
    }

    function it_is_not_attribute_removable_if_attribute_is_an_identifier(
        AttributeInterface $attribute
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
        $family->getCode()->willReturn('furniture');

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
        $family->getCode()->willReturn('gardening');
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
        $family->getCode()->willReturn('gardening');
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
        $family->getCode()->willReturn('gardening');

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
        $family->getCode()->willReturn('gardening');
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
        $family->getCode()->willReturn('gardening');

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
        $family->getCode()->willReturn('gardening');

        $this->setFamily($family);

        $this->getImage()->shouldReturn(null);
    }

    function it_gets_no_image_if_the_value_of_image_is_empty(
        FamilyInterface $family,
        AttributeInterface $attributeAsImage
    ) {
        $attributeAsImage->getCode()->willReturn('picture');
        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $family->getCode()->willReturn('gardening');

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
        $parent->getCode()->willReturn('model_1');
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
        ValueInterface $value,
        ValueInterface $otherValue
    ) {
        $value->getAttributeCode()->willReturn('value');
        $value->getScopeCode()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);

        $valueCollection = new WriteValueCollection([$value->getWrappedObject()]);
        $this->setValues($valueCollection);
        $productModel->getCode()->willReturn('model_parent');
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
        $productModel->getCode()->willReturn('model_parent');
        $this->setParent($productModel);
        $productModel->getVariationLevel()->willReturn(7);
        $this->getVariationLevel()->shouldReturn(8);
    }

    function it_has_a_product_model(ProductModelInterface $productModel)
    {
        $productModel->getCode()->willReturn('model_parent');
        $this->setParent($productModel);
        $this->getParent()->shouldReturn($productModel);
    }

    function it_has_a_family_variant(FamilyVariantInterface $familyVariant)
    {
        $familyVariant->getCode()->willReturn('by_size');
        $this->setFamilyVariant($familyVariant);
        $this->getFamilyVariant()->shouldReturn($familyVariant);
    }

    function it_can_reset_its_updated_state()
    {
        $this->setEnabled(false);
        $this->cleanup();
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_a_category_is_added(CategoryInterface $category)
    {
        $this->cleanup();

        $this->addCategory($category);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_an_already_existing_category_is_added(
        CategoryInterface $category
    ) {
        $this->setCategories(new ArrayCollection([$category->getWrappedObject()]));
        $this->cleanup();

        $this->addCategory($category);
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_a_category_is_removed(CategoryInterface $category)
    {
        $this->setCategories(new ArrayCollection([$category->getWrappedObject()]));
        $this->cleanup();

        $this->removeCategory($category);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_a_non_existing_category_is_removed(CategoryInterface $category)
    {
        $this->cleanup();

        $this->removeCategory($category);
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_setting_or_removing_categories(
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $this->setCategories(new ArrayCollection([$category1->getWrappedObject()]));
        $this->cleanup();

        $this->setCategories(new ArrayCollection([$category2->getWrappedObject()]));
        $this->isDirty()->shouldBe(true);
    }

    function it_is_updated_when_setting_the_same_categories(
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $this->setCategories(new ArrayCollection([$category1->getWrappedObject(), $category2->getWrappedObject()]));
        $this->cleanup();

        $this->setCategories(new ArrayCollection([$category2->getWrappedObject(), $category1->getWrappedObject()]));
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_a_group_is_added(GroupInterface $group)
    {
        $this->cleanup();

        $this->addGroup($group);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_an_existing_group_is_added(GroupInterface $group)
    {
        $this->setGroups(new ArrayCollection([$group->getWrappedObject()]));
        $this->cleanup();

        $this->addGroup($group);
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_a_group_is_removed(GroupInterface $group)
    {
        $this->setGroups(new ArrayCollection([$group->getWrappedObject()]));
        $this->cleanup();

        $this->removeGroup($group);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_a_non_existing_group_is_removed(GroupInterface $group)
    {
        $this->cleanup();

        $this->removeGroup($group);
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_setting_or_removing_groups(GroupInterface $group1, GroupInterface $group2)
    {
        $this->setGroups(new ArrayCollection([$group1->getWrappedObject()]));
        $this->cleanup();

        $this->setGroups(new ArrayCollection([$group2->getWrappedObject()]));
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_setting_the_same_groups(GroupInterface $group1, GroupInterface $group2)
    {
        $this->setGroups(new ArrayCollection([$group1->getWrappedObject(), $group2->getWrappedObject()]));
        $this->cleanup();

        $this->setGroups(new ArrayCollection([$group2->getWrappedObject(), $group1->getWrappedObject()]));
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_changing_the_identifier()
    {
        $this->setIdentifier('foo');
        $this->cleanup();

        $this->setIdentifier('baz');
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_setting_the_same_identifier()
    {
        $this->setIdentifier('foo');
        $this->cleanup();

        $this->setIdentifier('foo');
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_updating_the_status()
    {
        $this->cleanup();
        $this->setEnabled(false);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_the_status_is_not_updated()
    {
        $this->cleanup();
        $this->setEnabled(true);
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_updating_the_parent_model(
        ProductModelInterface $productModel,
        ProductModelInterface $otherProductModel
    ) {
        $productModel->getCode()->willReturn('former_parent');
        $this->setParent($productModel);
        $this->cleanup();

        $otherProductModel->getCode()->willReturn('new_parent');

        $this->setParent($otherProductModel);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_setting_the_same_parent_model(ProductModelInterface $parent)
    {
        $parent->getCode()->willReturn('parent');
        $this->setParent($parent);
        $this->cleanup();

        $this->setParent($parent);
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_changing_the_family_variant(
        FamilyVariantInterface $familyVariant,
        FamilyVariantInterface $otherFamilyVariant
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $this->setFamilyVariant($familyVariant);
        $this->cleanup();

        $otherFamilyVariant->getCode()->willReturn('by_color_and_size');
        $this->setFamilyVariant($otherFamilyVariant);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_setting_the_same_family_variant(
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $this->setFamilyVariant($familyVariant);
        $this->cleanup();

        $this->setFamilyVariant($familyVariant);
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_a_value_is_added()
    {
        $this->cleanup();
        $this->addValue(ScalarValue::value('name', 'My great product'));

        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_a_value_fails_to_be_added()
    {
        $this->addValue(ScalarValue::value('name', 'My great product'));
        $this->cleanup();

        $this->addValue(ScalarValue::value('name', 'Another name'));
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_a_value_is_removed()
    {
        $value = ScalarValue::value('name', 'My great product');
        $this->addValue($value);
        $this->cleanup();

        $this->removeValue($value);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_a_value_fails_to_be_removed()
    {
        $this->cleanup();
        $this->removeValue(ScalarValue::value('name', 'My great product'));

        $this->isDirty()->shouldBe(false);
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

        $this->isDirty()->shouldBe(true);
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
        $this->isDirty()->shouldBe(true);
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
        $this->isDirty()->shouldBe(false);
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
        $this->isDirty()->shouldBe(true);
    }

    function it_is_updated_when_filtering_quantified_associations()
    {
        $this->filterQuantifiedAssociations(['foo', 'bar'], ['baz']);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_updated_when_patching_quantified_associations(
        QuantifiedAssociationCollection $quantifiedAssociations
    ) {
        $quantifiedAssociations->normalize()->willReturn([
            'type' => [
                'products' => [
                    [
                        'identifier' => 'foo',
                        'quantity' => 2,
                    ]
                ],
                'product_models' => [
                    [
                        'identifier' => 'bar',
                        'quantity' => 1
                    ],
                ],
            ],
        ]);
        $this->mergeQuantifiedAssociations($quantifiedAssociations);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_updated_when_clearing_quantified_associations()
    {
        $this->isDirty()->shouldBe(false);
        $this->clearQuantifiedAssociations();
        $this->isDirty()->shouldBe(true);
    }

    function it_is_updated_when_adding_a_non_empty_association(
        AssociationInterface $association,
        AssociationTypeInterface $associationType
    ) {
        $associationType->getCode()->willReturn('X_SELL');
        $association->getAssociationType()->willReturn($associationType);
        $association->getProducts()->willReturn(new ArrayCollection([new Product()]));
        $this->cleanup();

        $association->setOwner($this)->shouldBeCalled();

        $this->addAssociation($association);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_adding_an_empty_association(
        AssociationInterface $association,
        AssociationTypeInterface $associationType
    ) {
        $associationType->getCode()->willReturn('X_SELL');
        $association->getAssociationType()->willReturn($associationType);
        $association->getProducts()->willReturn(new ArrayCollection());
        $association->getProductModels()->willReturn(new ArrayCollection());
        $association->getGroups()->willReturn(new ArrayCollection());
        $this->cleanup();


        $association->setOwner($this)->shouldBeCalled();

        $this->addAssociation($association);
        $this->isDirty()->shouldBe(false);
    }

    function it_is_not_updated_when_adding_an_already_existing_association()
    {
        $upsellAssociation = new ProductAssociation();
        $upsellType = new AssociationType();
        $upsellType->setCode('UPSELL');
        $upsellAssociation->setAssociationType($upsellType);

        $this->addAssociation($upsellAssociation);
        $this->cleanup();

        $this
            ->shouldThrow(\LogicException::class)
            ->during('addAssociation', [$upsellAssociation]);

        $this->isDirty()->shouldBe(false);
    }

    function it_throws_an_exception_if_a_similar_association_already_exists()
    {
        $upsellAssociation = new ProductAssociation();
        $upsellType = new AssociationType();
        $upsellType->setCode('UPSELL');
        $upsellAssociation->setAssociationType($upsellType);

        $anotherUpsellAssociation = new ProductAssociation();
        $anotherUpsellType = new AssociationType();
        $anotherUpsellType->setCode('UPSELL');
        $anotherUpsellAssociation->setAssociationType($anotherUpsellType);

        $this->addAssociation($upsellAssociation);

        $this
            ->shouldThrow(\LogicException::class)
            ->during('addAssociation', [$anotherUpsellAssociation]);
    }

    function it_is_updated_when_a_non_empty_association_is_removed()
    {
        $upsellAssociation = new ProductAssociation();
        $upsellType = new AssociationType();
        $upsellType->setCode('UPSELL');
        $upsellAssociation->setAssociationType($upsellType);
        $upsellAssociation->addProduct(new Product());

        $this->addAssociation($upsellAssociation);
        $this->cleanup();

        $this->removeAssociation($upsellAssociation);
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_an_empty_association_is_removed()
    {
        $upsellAssociation = new ProductAssociation();
        $upsellType = new AssociationType();
        $upsellType->setCode('UPSELL');
        $upsellAssociation->setAssociationType($upsellType);

        $this->addAssociation($upsellAssociation);
        $this->cleanup();

        $this->removeAssociation($upsellAssociation);
        $this->isDirty()->shouldBe(false);
    }

    function it_is_not_updated_when_removing_a_non_existent_association()
    {
        $upsellAssociation = new ProductAssociation();
        $upsellType = new AssociationType();
        $upsellType->setCode('UPSELL');
        $upsellAssociation->setAssociationType($upsellType);

        $this->cleanup();

        $this->removeAssociation($upsellAssociation);
        $this->isDirty()->shouldBe(false);
    }

    public function it_knows_if_it_has_an_association_for_a_given_type(): void
    {
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->hasAssociationForTypeCode('x_sell')->shouldReturn(false);

        $this->addAssociation($xsellAssociation);

        $this->hasAssociationForTypeCode('x_sell')->shouldReturn(true);
    }

    public function it_adds_a_product_to_an_association(
        AssociationInterface $association
    ): void {
        $product = new Product();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->hasProduct($product)->willReturn(false);
        $association->getProducts()->willReturn(new ArrayCollection([]));
        $association->getProductModels()->willReturn(new ArrayCollection([]));
        $association->getGroups()->willReturn(new ArrayCollection([]));
        $association->setOwner($this)->willReturn($association);
        $this->addAssociation($association);

        $association->addProduct($product)->shouldBeCalled();

        $this->addAssociatedProduct($product, 'x_sell');
    }

    public function it_is_updated_if_a_product_is_added_to_an_association(): void {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->addAssociatedProduct($product, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    public function it_is_not_updated_if_a_product_to_add_to_an_association_already_exists(): void {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProduct($product);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->addAssociatedProduct($product, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    public function it_removes_a_product_from_an_association(
        AssociationInterface $association
    ): void {
        $product = new Product();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->hasProduct($product)->willReturn(true);
        $association->getProducts()->willReturn(new ArrayCollection([$product]));
        $association->getProductModels()->willReturn(new ArrayCollection([]));
        $association->getGroups()->willReturn(new ArrayCollection([]));
        $association->setOwner($this)->willReturn($association);
        $this->addAssociation($association);

        $association->removeProduct($product)->shouldBeCalled();

        $this->removeAssociatedProduct($product, 'x_sell');
    }

    public function it_is_updated_if_a_product_is_removed_from_an_association(): void {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProduct($product);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->removeAssociatedProduct($product, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    public function it_is_not_updated_if_a_product_to_remove_from_an_association_does_not_exist(): void {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->removeAssociatedProduct($product, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    public function it_returns_associated_products_in_terms_of_an_association_type(): void
    {
        $plate = new Product();
        $spoon = new Product();

        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProduct($plate);
        $xsellAssociation->addProduct($spoon);

        $this->addAssociation($xsellAssociation);

        $this->getAssociatedProducts('x_sell')->shouldBeLike(new ArrayCollection([$plate, $spoon]));
        $this->getAssociatedProducts('another_association_type')->shouldReturn(null);
    }

    public function it_adds_a_product_model_to_an_association(
        AssociationInterface $association
    ): void {
        $productModel = new ProductModel();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->hasProduct($productModel)->willReturn(false);
        $association->getProducts()->willReturn(new ArrayCollection([]));
        $association->getProductModels()->willReturn(new ArrayCollection([]));
        $association->getGroups()->willReturn(new ArrayCollection([]));
        $association->setOwner($this)->willReturn($association);
        $this->addAssociation($association);

        $association->addProductModel($productModel)->shouldBeCalled();

        $this->addAssociatedProductModel($productModel, 'x_sell');
    }

    public function it_is_updated_if_a_product_model_is_added_to_an_association(): void {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->addAssociatedProductModel($productModel, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    public function it_is_not_updated_if_a_product_model_to_add_to_an_association_already_exists(): void {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProductModel($productModel);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->addAssociatedProductModel($productModel, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    public function it_removes_a_product_model_from_an_association(
        AssociationInterface $association
    ): void {
        $productModel = new ProductModel();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->getProducts()->willReturn(new ArrayCollection([]));
        $association->getProductModels()->willReturn(new ArrayCollection([$productModel]));
        $association->getGroups()->willReturn(new ArrayCollection([]));
        $association->setOwner($this)->willReturn($association);

        $this->addAssociation($association);

        $association->removeProductModel($productModel)->shouldBeCalled();

        $this->removeAssociatedProductModel($productModel, 'x_sell');
    }

    public function it_is_updated_if_a_product_model_is_removed_from_an_association(): void {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProductModel($productModel);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->removeAssociatedProductModel($productModel, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    public function it_is_not_updated_if_a_product_model_to_remove_from_an_association_does_not_exist(): void {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->removeAssociatedProductModel($productModel, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    public function it_returns_associated_product_models_in_terms_of_an_association_type(): void
    {
        $plate = new ProductModel();
        $spoon = new ProductModel();

        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProductModel($plate);
        $xsellAssociation->addProductModel($spoon);

        $this->addAssociation($xsellAssociation);

        $this->getAssociatedProductModels('x_sell')->shouldBeLike(new ArrayCollection([$plate, $spoon]));
        $this->getAssociatedProductModels('another_association_type')->shouldReturn(null);
    }

    public function it_adds_a_group_to_an_association(
        AssociationInterface $association
    ): void {
        $group = new Group();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->getProducts()->willReturn(new ArrayCollection([]));
        $association->getProductModels()->willReturn(new ArrayCollection([]));
        $association->getGroups()->willReturn(new ArrayCollection([]));
        $association->setOwner($this)->willReturn($association);

        $this->addAssociation($association);

        $association->addGroup($group)->shouldBeCalled();

        $this->addAssociatedGroup($group, 'x_sell');
    }

    public function it_is_updated_if_a_group_is_added_to_an_association(): void {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->addAssociatedGroup($group, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    public function it_is_not_updated_if_a_group_to_add_to_an_association_already_exists(): void {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addGroup($group);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->addAssociatedGroup($group, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    public function it_removes_a_group_from_an_association(
        AssociationInterface $association
    ): void {
        $group = new Group();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->getProducts()->willReturn(new ArrayCollection([]));
        $association->getProductModels()->willReturn(new ArrayCollection([]));
        $association->getGroups()->willReturn(new ArrayCollection([$group]));
        $association->setOwner($this)->willReturn($association);
        $this->addAssociation($association);

        $association->removeGroup($group)->shouldBeCalled();

        $this->removeAssociatedGroup($group, 'x_sell');
    }

    public function it_is_updated_if_a_group_is_removed_from_an_association(): void {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addGroup($group);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->removeAssociatedGroup($group, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    public function it_is_not_updated_if_a_group_to_remove_from_an_association_does_not_exist(): void {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->addAssociation($xsellAssociation);
        $this->cleanup();

        $this->removeAssociatedGroup($group, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    public function it_returns_associated_groups_in_terms_of_an_association_type(): void
    {
        $plate = new Group();
        $spoon = new Group();

        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addGroup($plate);
        $xsellAssociation->addGroup($spoon);

        $this->addAssociation($xsellAssociation);

        $this->getAssociatedGroups('x_sell')->shouldBeLike(new ArrayCollection([$plate, $spoon]));
        $this->getAssociatedGroups('another_association_type')->shouldReturn(null);
    }
}
