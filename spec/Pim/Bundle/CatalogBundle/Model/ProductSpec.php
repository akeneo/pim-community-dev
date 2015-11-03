<?php

namespace spec\Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;

class ProductSpec extends ObjectBehavior
{
    function it_has_family(\Pim\Component\Catalog\Model\FamilyInterface $family)
    {
        $family->getId()->willReturn(42);
        $this->setFamily($family);
        $this->getFamily()->shouldReturn($family);
        $this->getFamilyId()->shouldReturn(42);
    }

    function it_belongs_to_categories(\Pim\Component\Catalog\Model\CategoryInterface $category1, \Pim\Component\Catalog\Model\CategoryInterface $category2)
    {
        $this->addCategory($category1);
        $this->getCategories()->shouldHaveCount(1);
        $this->addCategory($category2);
        $this->getCategories()->shouldHaveCount(2);
    }

    function it_returns_association_from_an_association_type(
        Association $assoc1,
        Association $assoc2,
        \Pim\Component\Catalog\Model\AssociationTypeInterface $assocType1,
        AssociationTypeInterface $assocType2
    ) {
        $assoc1->getAssociationType()->willReturn($assocType1);
        $assoc2->getAssociationType()->willReturn($assocType2);

        $this->setAssociations([$assoc1, $assoc2]);
        $this->getAssociationForType($assocType1)->shouldReturn($assoc1);
    }

    function it_returns_association_from_an_association_type_code(
        Association $assoc1,
        Association $assoc2,
        \Pim\Component\Catalog\Model\AssociationTypeInterface $assocType1,
        \Pim\Component\Catalog\Model\AssociationTypeInterface $assocType2
    ) {
        $assocType1->getCode()->willReturn('ASSOC_TYPE_1');
        $assocType2->getCode()->willReturn('ASSOC_TYPE_2');
        $assoc1->getAssociationType()->willReturn($assocType1);
        $assoc2->getAssociationType()->willReturn($assocType2);

        $this->setAssociations([$assoc1, $assoc2]);
        $this->getAssociationForTypeCode('ASSOC_TYPE_2')->shouldReturn($assoc2);
    }

    function it_returns_null_when_i_try_to_get_an_association_with_an_empty_collection(
        AssociationTypeInterface $assocType1
    ) {
        $this->setAssociations([]);
        $this->getAssociationForType($assocType1)->shouldReturn(null);
    }

    function it_has_not_attribute_in_family_without_family(\Pim\Component\Catalog\Model\AttributeInterface $attribute)
    {
        $this->hasAttributeInfamily($attribute)->shouldReturn(false);
    }

    function it_has_not_attribute_in_family(\Pim\Component\Catalog\Model\AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $attributes)
    {
        $attributes->contains($attribute)->willReturn(false);
        $family->getId()->willReturn(42);
        $family->getAttributes()->willReturn($attributes);
        $this->setFamily($family);
        $this->hasAttributeInfamily($attribute)->shouldReturn(false);
    }

    function it_has_attribute_in_family(\Pim\Component\Catalog\Model\AttributeInterface $attribute, FamilyInterface $family, ArrayCollection $attributes)
    {
        $attributes->contains($attribute)->willReturn(true);
        $family->getId()->willReturn(42);
        $family->getAttributes()->willReturn($attributes);
        $this->setFamily($family);
        $this->hasAttributeInfamily($attribute)->shouldReturn(true);
    }

    function it_has_not_attribute_in_group_without_groups(\Pim\Component\Catalog\Model\AttributeInterface $attribute)
    {
        $this->hasAttributeInVariantGroup($attribute)->shouldReturn(false);
    }

    function it_has_not_attribute_in_a_non_variant_group(AttributeInterface $attribute, GroupInterface $group, GroupTypeInterface $groupType)
    {
        $groupType->isVariant()->willReturn(false);
        $group->addProduct($this)->willReturn($this);
        $group->getType()->willReturn($groupType);

        $this->addGroup($group);
        $this->hasAttributeInVariantGroup($attribute)->shouldReturn(false);
    }

    function it_has_attribute_in_a_variant_group(AttributeInterface $attribute, \Pim\Component\Catalog\Model\GroupInterface $group, GroupTypeInterface $groupType, ArrayCollection $groupAttributes)
    {
        $groupType->isVariant()->willReturn(true);
        $groupAttributes->contains($attribute)->willReturn(true);
        $group->getType()->willReturn($groupType);
        $group->getAxisAttributes()->willReturn($groupAttributes);
        $group->addProduct($this)->willReturn($this);

        $this->addGroup($group);
        $this->hasAttributeInVariantGroup($attribute)->shouldReturn(true);
    }

    function it_has_attribute_in_a_variant_group_template(AttributeInterface $attribute, \Pim\Component\Catalog\Model\GroupInterface $group, GroupTypeInterface $groupType, ArrayCollection $groupAttributes, ProductTemplateInterface $template)
    {
        $groupType->isVariant()->willReturn(true);
        $groupAttributes->contains($attribute)->willReturn(false);
        $template->hasValueForAttribute($attribute)->shouldBeCalled()->willReturn(true);
        $group->getType()->willReturn($groupType);
        $group->getProductTemplate()->willReturn($template);
        $group->getAxisAttributes()->willReturn($groupAttributes);
        $group->addProduct($this)->willReturn($this);

        $this->addGroup($group);
        $this->hasAttributeInVariantGroup($attribute)->shouldReturn(true);
    }

    function it_is_not_attribute_editable_without_family(AttributeInterface $attribute)
    {
        $this->isAttributeEditable($attribute)->shouldReturn(false);
    }

    function it_is_not_attribute_editable_with_group_containing_attribute(\Pim\Component\Catalog\Model\AttributeInterface $attribute, \Pim\Component\Catalog\Model\GroupInterface $group, GroupTypeInterface $groupType, ArrayCollection $groupAttributes)
    {
        $groupType->isVariant()->willReturn(true);
        $groupAttributes->contains($attribute)->willReturn(true);
        $group->getType()->willReturn($groupType);
        $group->getAxisAttributes()->willReturn($groupAttributes);
        $group->addProduct($this)->willReturn($this);

        $this->addGroup($group);
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
        $attribute->getAttributeType()->willReturn(AttributeTypes::IDENTIFIER);

        $this->isAttributeRemovable($attribute)->shouldReturn(false);
    }

    function it_is_not_attribute_removable_with_family_containing_attribute(AttributeInterface $attribute, \Pim\Component\Catalog\Model\FamilyInterface $family, ArrayCollection $familyAttributes)
    {
        $familyAttributes->contains($attribute)->willReturn(true);
        $family->getId()->willReturn(42);
        $family->getAttributes()->willReturn($familyAttributes);

        $this->setFamily($family);
        $this->isAttributeRemovable($attribute)->shouldReturn(false);
    }

    function it_is_not_attribute_removable_with_group_containing_attribute(AttributeInterface $attribute, \Pim\Component\Catalog\Model\GroupInterface $group, GroupTypeInterface $groupType, ArrayCollection $groupAttributes)
    {
        $groupType->isVariant()->willReturn(true);
        $groupAttributes->contains($attribute)->willReturn(true);
        $group->getType()->willReturn($groupType);
        $group->getAxisAttributes()->willReturn($groupAttributes);
        $group->addProduct($this)->willReturn($this);

        $this->addGroup($group);
        $this->isAttributeRemovable($attribute)->shouldReturn(false);
    }

    function it_is_attribute_removable(AttributeInterface $attribute)
    {
        $this->isAttributeRemovable($attribute)->shouldReturn(true);
    }
}
