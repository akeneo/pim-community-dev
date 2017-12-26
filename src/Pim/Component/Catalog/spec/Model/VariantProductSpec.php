<?php

namespace spec\Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\Association;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

class VariantProductSpec extends ObjectBehavior
{
    function it_has_family(FamilyInterface $family)
    {
        $family->getId()->willReturn(42);
        $this->setFamily($family);
        $this->getFamily()->shouldReturn($family);
        $this->getFamilyId()->shouldReturn(42);
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

    function it_belongs_to_categories(CategoryInterface $category1, CategoryInterface $category2)
    {
        $this->addCategory($category1);
        $this->getCategories()->shouldHaveCount(1);
        $this->addCategory($category2);
        $this->getCategories()->shouldHaveCount(2);
    }

    function it_returns_association_from_an_association_type(
        Association $assoc1,
        Association $assoc2,
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
        Association $assoc1,
        Association $assoc2,
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

    function it_is_attribute_editable_with_family_containing_attribute(AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $familyAttributes)
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

    function it_is_not_attribute_removable_with_family_containing_attribute(AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $familyAttributes)
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

    function it_has_the_values_of_the_variation(
        ValueCollectionInterface $valueCollection
    ) {
        $this->setValues($valueCollection);

        $this->getValuesForVariation()->shouldBeLike($valueCollection);
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

    function it_returns_the_youngest_updated_at_between_variant_products_and_ancestors(
        ProductModelInterface $subProductModel,
        ProductModelInterface $rootProductModel
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $date1 = new \DateTime('now', new \DateTimeZone('UTC'));
        $date1->modify('-1 day');

        $date2 = new \DateTime('now', new \DateTimeZone('UTC'));
        $date2->modify('-2 day');

        $this->setUpdated($date2);
        $subProductModel->getUpdated()->willReturn($date1);
        $rootProductModel->getUpdated()->willReturn($now);

        $this->setParent($subProductModel);
        $subProductModel->getParent()->willReturn($rootProductModel);
        $rootProductModel->getParent()->willReturn(null);

        $this->getUpdated()->shouldReturn($now);
    }
}
