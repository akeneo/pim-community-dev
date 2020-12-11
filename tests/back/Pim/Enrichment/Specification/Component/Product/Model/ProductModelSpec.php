<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

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

    function it_adds_a_value(WriteValueCollection $values)
    {
        $values->getIterator()->willReturn(new \ArrayIterator([]));
        $this->setValues($values);

        $value = ScalarValue::value('foobar', 'baz');
        $values->add($value)->shouldBeCalled();

        $this->addValue($value)->shouldReturn($this);
    }

    function it_removes_a_value(WriteValueCollection $values)
    {
        $value = ScalarValue::value('foobar', 'baz');
        $values->getIterator()->willReturn(new \ArrayIterator([$value]));
        $this->setValues($values);

        $values->remove($value)->shouldBeCalled();

        $this->removeValue($value)->shouldReturn($this);
    }

    function it_gets_the_codes_of_the_product_model_categories(
        CategoryInterface $categorie
    ) {
        $this->addCategory($categorie);

        $categorie->getCode()->willReturn('foobar');

        $this->getCategoryCodes()->shouldReturn(['foobar']);
    }

    function it_returns_the_code_as_identifier()
    {
        $this->getIdentifier()->shouldReturn(null);

        $this->setCode('the_code');
        $this->getIdentifier()->shouldReturn('the_code');
    }

    function it_gets_the_label_regardless_of_the_specified_scope_if_the_attribute_as_label_is_not_scopable(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(false);

        $nameValue = ScalarValue::localizableValue('name', 'Petit outil agricole authentique', 'fr_FR');
        $values = new WriteValueCollection([$nameValue]);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petit outil agricole authentique');
    }

    function it_gets_the_label_if_the_scope_is_specified_and_the_attribute_as_label_is_scopable(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(true);

        $mobileNameValue = ScalarValue::scopableLocalizableValue('name', 'Petite pelle', 'mobile', 'fr_FR');
        $values = new WriteValueCollection([$mobileNameValue]);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petite pelle');
    }

    function it_gets_the_code_as_label_if_there_is_no_attribute_as_label(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn(null);

        $this->setFamilyVariant($familyVariant);
        $this->setCode('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_code_as_label_if_the_label_value_is_not_set(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $this->setFamilyVariant($familyVariant);
        $this->setValues(new WriteValueCollection());
        $this->setCode('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_code_as_label_if_no_scope_is_specified_but_the_attribute_as_label_is_scopable(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values = new WriteValueCollection([
            ScalarValue::scopableLocalizableValue('name', 'Petit outil agricole', 'mobile', 'fr_FR')
        ]);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_label_if_no_locale_is_specified(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isUnique()->willReturn(false);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values = new WriteValueCollection(
            [
                ScalarValue::scopableLocalizableValue('name', 'Petit outil agricole', 'mobile', 'fr_FR')
            ]
        );

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);
        $this->setCode('shovel');

        $this->getLabel()->shouldReturn('shovel');
    }

    function it_gets_the_image_of_the_product_model(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsImage,
        FileInfoInterface $fileInfo
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $attributeAsImage->getCode()->willReturn('picture');
        $attributeAsImage->isUnique()->willReturn(false);

        $pictureValue = MediaValue::value('picture', $fileInfo->getWrappedObject());
        $values = new WriteValueCollection([$pictureValue]);

        $this->setFamilyVariant($familyVariant);
        $this->setValues($values);

        $this->getImage()->shouldReturn($pictureValue);
    }

    function it_gets_no_image_if_there_is_no_attribute_as_image(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsImage()->willReturn(null);

        $this->setFamilyVariant($familyVariant);

        $this->getImage()->shouldReturn(null);
    }

    function it_gets_no_image_if_the_value_of_image_is_empty(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsImage
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $attributeAsImage->getCode()->willReturn('picture');

        $this->setFamilyVariant($familyVariant);
        $values = new WriteValueCollection();
        $this->setValues($values);

        $this->getImage()->shouldReturn(null);
    }

    function it_has_the_values_of_the_variation()
    {
        $valueCollection = new WriteValueCollection([
            ScalarValue::value('foo', 'bar')
        ]);
        $this->setValues($valueCollection);

        $this->getValuesForVariation()->shouldBeLike($valueCollection);
    }

    function it_has_values(ProductModelInterface $productModel)
    {
        $value = ScalarValue::value('foo', 'bar');
        $valueCollection = new WriteValueCollection([$value]);
        $this->setValues($valueCollection);

        $otherValue = OptionsValue::localizableValue('color', ['red', 'blue'], 'en_US');
        $parentValues = new WriteValueCollection([$otherValue]);
        $productModel->getCode()->willReturn('parent');
        $productModel->getValuesForVariation()->willReturn($parentValues);
        $productModel->getParent()->willReturn(null);
        $this->setParent($productModel);

        $this->getValues()->shouldBeLike(new WriteValueCollection([$value, $otherValue]));
    }

    function it_gets_label_when_casting_object_as_string(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel
    ) {
        $familyVariant->getCode()->willReturn('by_size');
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $this->setFamilyVariant($familyVariant);
        $this->setValues(new WriteValueCollection());
        $this->setCode('shovel');

        $this->__toString()->shouldReturn('shovel');
    }

    function it_saves_empty_raw_values()
    {
        $this->setRawValues([]);
        $this->getRawValues()->shouldReturn([]);
    }

    // Quantified associations
    function it_hydrates_quantified_associations()
    {
        $idMapping = $this->idMapping();
        $this->rawQuantifiedAssociations = [
            'PACK' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2]
                ],
                'product_models' => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2]
                ],
            ]
        ];
        $this->hydrateQuantifiedAssociations($idMapping, $idMapping, ['PACK']);
        $this->normalizeQuantifiedAssociations()->shouldReturn([
            'PACK' => [
                'products'       => [
                    ['identifier' => 'entity_1', 'quantity' => 1],
                    ['identifier' => 'entity_2', 'quantity' => 2]
                ],
                'product_models' => [
                    ['identifier' => 'entity_1', 'quantity' => 1],
                    ['identifier' => 'entity_2', 'quantity' => 2]
                ],
            ]
        ]);
    }

    function it_saves_a_quantified_associations()
    {
        $idMapping = $this->idMapping();
        $this->rawQuantifiedAssociations = [
            'PACK' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2]
                ],
                'product_models' => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2]
                ],
            ]
        ];
        $this->hydrateQuantifiedAssociations($idMapping, $idMapping, ['PACK']);
        $this->normalizeQuantifiedAssociations()->shouldReturn([
            'PACK' => [
                'products'       => [
                    ['identifier' => 'entity_1', 'quantity' => 1],
                    ['identifier' => 'entity_2', 'quantity' => 2]
                ],
                'product_models' => [
                    ['identifier' => 'entity_1', 'quantity' => 1],
                    ['identifier' => 'entity_2', 'quantity' => 2]
                ],
            ]
        ]);
    }

    function it_filter_quantified_associations_during_hydration()
    {
        $idMapping = $this->idMapping();
        $this->rawQuantifiedAssociations = [
            'PACK' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2],
                    ['id' => 3, 'quantity' => 2],
                ],
                'product_models' => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2],
                    ['id' => 4, 'quantity' => 2],
                ],
            ],
            'NON_EXISTING_ASSOCIATION_TYPE' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                ],
                'product_models'       => [
                    ['id' => 1, 'quantity' => 1],
                ],
            ],
        ];

        $this->hydrateQuantifiedAssociations($idMapping, $idMapping, ['PACK']);
        $this->normalizeQuantifiedAssociations()->shouldReturn([
            'PACK' => [
                'products'       => [
                    ['identifier' => 'entity_1', 'quantity' => 1],
                    ['identifier' => 'entity_2', 'quantity' => 2]
                ],
                'product_models' => [
                    ['identifier' => 'entity_1', 'quantity' => 1],
                    ['identifier' => 'entity_2', 'quantity' => 2]
                ],
            ]
        ]);
    }

    // Product quantified associations
    function it_returns_an_empty_list_of_quantified_association_product_ids_if_the_raw_quantified_associations_have_not_been_hydrated()
    {
        $this->getQuantifiedAssociationsProductIds()->shouldReturn([]);
    }

    function it_returns_an_empty_list_of_quantified_association_product_identifiers_if_the_raw_quantified_associations_have_not_been_hydrated()
    {
        $this->rawQuantifiedAssociations = $this->someRawQuantifiedAssociations();
        $this->getQuantifiedAssociationsProductIdentifiers()->shouldReturn([]);
    }

    // Product model quantified associations
    function it_returns_an_empty_list_of_quantified_association_product_model_ids_if_the_raw_quantified_associations_have_not_been_hydrated()
    {
        $this->getQuantifiedAssociationsProductModelIds()->shouldReturn([]);
    }

    function it_returns_an_empty_list_of_quantified_association_product_model_codes_if_the_raw_quantified_associations_have_not_been_hydrated()
    {
        $this->rawQuantifiedAssociations = $this->someRawQuantifiedAssociations();
        $this->getQuantifiedAssociationsProductModelCodes()->shouldReturn([]);
    }

    function it_is_updated_when_changing_the_code()
    {
        $this->setCode('foo');
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_when_setting_the_same_code()
    {
        $this->setCode('foo');
        $this->cleanup();

        $this->setCode('foo');
        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_updating_the_parent_model(
        ProductModelInterface $productModel,
        ProductModelInterface $otherProductModel
    ) {
        $productModel->getCode()->willReturn('parent');
        $this->setParent($productModel);
        $this->cleanup();

        $otherProductModel->getCode()->willReturn('other_parent');
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

    function it_is_updated_when_a_value_is_added()
    {
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
        $this->removeValue(ScalarValue::value('name', 'My great product'));

        $this->isDirty()->shouldBe(false);
    }

    function it_is_updated_when_setting_new_values()
    {
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
        $quantifiedAssociations->normalize()->willReturn(
            [
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
            ]
        );
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
    }

    private function someRawQuantifiedAssociations(): array
    {
        return [
            'PACK' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2]
                ],
                'product_models' => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2]
                ],
            ]
        ];
    }

    private function idMapping(): IdMapping
    {
        return IdMapping::createFromMapping([1 => 'entity_1', 2 => 'entity_2']);
    }
}
