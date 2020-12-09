<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class PublishedProductSpec extends ObjectBehavior
{
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
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);

        $nameValue->getData()->willReturn('Petit outil agricole authentique');

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier('shovel');

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
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);

        $nameValue->getData()->willReturn('Petit outil agricole authentique');

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier('shovel');

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
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(true);

        $values->getByCodes('name', 'mobile', 'fr_FR')->willReturn($nameValue);

        $nameValue->getData()->willReturn('Petite pelle');

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR', 'mobile')->shouldReturn('Petite pelle');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_family(
        WriteValueCollection $values,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $this->setFamily(null);
        $this->setValues($values);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_attribute_as_label(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        WriteValueCollection $values,
        ValueInterface $identifier
    ) {
        $family->getAttributeAsLabel()->willReturn(null);

        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier('shovel');

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_the_label_value_is_null(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        WriteValueCollection $values,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttributeCode()->willReturn('name');

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willReturn(null);

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier('shovel');

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
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);
        $attributeAsLabel->isScopable()->willReturn(false);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);

        $nameValue->getData()->willReturn(null);

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier('shovel');

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

    function it_knows_if_it_has_an_association_for_a_given_type(): void
    {
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->hasAssociationForTypeCode('x_sell')->shouldReturn(false);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));

        $this->hasAssociationForTypeCode('x_sell')->shouldReturn(true);
    }

    function it_adds_a_product_to_an_association(
        AssociationInterface $association
    ): void {
        $product = new Product();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->hasProduct($product)->willReturn(false);
        $this->setAssociations(new ArrayCollection([$association->getWrappedObject()]));

        $association->addProduct($product)->shouldBeCalled();

        $this->addAssociatedProduct($product, 'x_sell');
    }

    function it_is_updated_if_a_product_is_added_to_an_association(): void {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->addAssociatedProduct($product, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_if_a_product_to_add_to_an_association_already_exists(): void {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProduct($product);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->addAssociatedProduct($product, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    function it_removes_a_product_from_an_association(
        AssociationInterface $association
    ): void {
        $product = new Product();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->hasProduct($product)->willReturn(true);
        $this->setAssociations(new ArrayCollection([$association->getWrappedObject()]));

        $association->removeProduct($product)->shouldBeCalled();

        $this->removeAssociatedProduct($product, 'x_sell');
    }

    function it_is_updated_if_a_product_is_removed_from_an_association(): void {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProduct($product);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->removeAssociatedProduct($product, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_if_a_product_to_remove_from_an_association_does_not_exist(): void {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->removeAssociatedProduct($product, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    function it_returns_associated_products_in_terms_of_an_association_type(): void
    {
        $plate = new Product();
        $spoon = new Product();

        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProduct($plate);
        $xsellAssociation->addProduct($spoon);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));

        $this->getAssociatedProducts('x_sell')->shouldBeLike(new ArrayCollection([$plate, $spoon]));
    }

    function it_adds_a_product_model_to_an_association(
        AssociationInterface $association
    ): void {
        $productModel = new ProductModel();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->hasProduct($productModel)->willReturn(false);
        $this->setAssociations(new ArrayCollection([$association->getWrappedObject()]));

        $association->getProductModels()->willReturn(new ArrayCollection([]));
        $association->addProductModel($productModel)->shouldBeCalled();

        $this->addAssociatedProductModel($productModel, 'x_sell');
    }

    function it_is_updated_if_a_product_model_is_added_to_an_association(): void {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->addAssociatedProductModel($productModel, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_if_a_product_model_to_add_to_an_association_already_exists(): void {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProductModel($productModel);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->addAssociatedProductModel($productModel, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    function it_removes_a_product_model_from_an_association(
        AssociationInterface $association
    ): void {
        $productModel = new ProductModel();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);

        $association->getProductModels()->willReturn(new ArrayCollection([$productModel]));
        $this->setAssociations(new ArrayCollection([$association->getWrappedObject()]));

        $association->removeProductModel($productModel)->shouldBeCalled();

        $this->removeAssociatedProductModel($productModel, 'x_sell');
    }

    function it_is_updated_if_a_product_model_is_removed_from_an_association(): void {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProductModel($productModel);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->removeAssociatedProductModel($productModel, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_if_a_product_model_to_remove_from_an_association_does_not_exist(): void {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->removeAssociatedProductModel($productModel, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    function it_returns_associated_product_models_in_terms_of_an_association_type(): void
    {
        $plate = new ProductModel();
        $spoon = new ProductModel();

        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProductModel($plate);
        $xsellAssociation->addProductModel($spoon);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));

        $this->getAssociatedProductModels('x_sell')->shouldBeLike(new ArrayCollection([$plate, $spoon]));
    }

    function it_adds_a_group_to_an_association(
        AssociationInterface $association
    ): void {
        $group = new Group();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->getGroups()->willReturn(new ArrayCollection([]));
        $this->setAssociations(new ArrayCollection([$association->getWrappedObject()]));

        $association->addGroup($group)->shouldBeCalled();

        $this->addAssociatedGroup($group, 'x_sell');
    }

    function it_is_updated_if_a_group_is_added_to_an_association(): void {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->addAssociatedGroup($group, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_if_a_group_to_add_to_an_association_already_exists(): void {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addGroup($group);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->addAssociatedGroup($group, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    function it_removes_a_group_from_an_association(
        AssociationInterface $association
    ): void {
        $group = new Group();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->getAssociationType()->willReturn($xsellType);
        $association->getGroups()->willReturn(new ArrayCollection([$group]));
        $this->setAssociations(new ArrayCollection([$association->getWrappedObject()]));

        $association->removeGroup($group)->shouldBeCalled();

        $this->removeAssociatedGroup($group, 'x_sell');
    }

    function it_is_updated_if_a_group_is_removed_from_an_association(): void {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addGroup($group);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->removeAssociatedGroup($group, 'x_sell');
        $this->isDirty()->shouldBe(true);
    }

    function it_is_not_updated_if_a_group_to_remove_from_an_association_does_not_exist(): void {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));
        $this->cleanup();

        $this->removeAssociatedGroup($group, 'x_sell');
        $this->isDirty()->shouldBe(false);
    }

    function it_returns_associated_groups_in_terms_of_an_association_type(): void
    {
        $plate = new Group();
        $spoon = new Group();

        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addGroup($plate);
        $xsellAssociation->addGroup($spoon);

        $this->setAssociations(new ArrayCollection([$xsellAssociation]));

        $this->getAssociatedGroups('x_sell')->shouldBeLike(new ArrayCollection([$plate, $spoon]));
    }
}
