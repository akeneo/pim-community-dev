<?php

namespace spec\Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\Association;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

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
        Association $assoc1,
        Association $assoc2,
        AssociationTypeInterface $assocType1,
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
        AssociationTypeInterface $assocType1,
        AssociationTypeInterface $assocType2
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

    function it_gets_the_label_of_the_product(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);
        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $nameValue->getData()->willReturn('Petit outil agricole authentique');

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('Petit outil agricole authentique');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_family(
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $this->setFamily(null);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_there_is_no_attribute_as_label(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $identifier
    ) {
        $family->getAttributeAsLabel()->willReturn(null);
        $family->getId()->willReturn(42);

        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
        $values->add($identifier)->shouldBeCalled();

        $this->setFamily($family);
        $this->setValues($values);
        $this->setIdentifier($identifier);

        $this->getLabel('fr_FR')->shouldReturn('shovel');
    }

    function it_gets_the_identifier_as_label_if_the_label_value_is_null(
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ValueCollectionInterface $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);

        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
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
        ValueCollectionInterface $values,
        ValueInterface $nameValue,
        ValueInterface $identifier
    ) {
        $identifier->getData()->willReturn('shovel');
        $identifier->getAttribute()->willReturn($attributeAsLabel);

        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getId()->willReturn(42);
        $attributeAsLabel->getCode()->willReturn('name');
        $attributeAsLabel->isLocalizable()->willReturn(true);

        $values->getByCodes('name', null, 'fr_FR')->willReturn($nameValue);
        $values->removeByAttribute($attributeAsLabel)->shouldBeCalled();
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
        ValueCollectionInterface $values,
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
        ValueCollectionInterface $values
    ) {
        $attributeAsImage->getCode()->willReturn('picture');

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $family->getId()->willReturn(42);

        $values->getByCodes('picture', null, null)->willReturn(null);

        $this->setFamily($family);
        $this->setValues($values);

        $this->getImage()->shouldReturn(null);
    }
}
