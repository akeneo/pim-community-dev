<?php

namespace spec\Pim\Component\Catalog\EntityWithFamilyVariant;

use Pim\Component\Catalog\EntityWithFamilyVariant\CheckAttributeEditable;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Prophecy\Argument;

class CheckAttributeEditableSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CheckAttributeEditable::class);
    }

    function it_is_editable_if_the_entity_has_no_family(
        EntityWithFamilyInterface $entity,
        AttributeInterface $attribute
    ) {
        $entity->getFamily()->willReturn(null);
        $this->isEditable($entity, $attribute)->shouldReturn(true);
    }

    function it_is_not_editable_if_the_attribute_is_not_part_of_the_family(
        EntityWithFamilyInterface $entity,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $entity->getFamily()->willReturn($family);
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(false);
        $this->isEditable($entity, $attribute)->shouldReturn(false);
    }

    function it_is_editable_if_the_attribute_is_part_of_the_family_and_the_product_is_not_variant(
        ProductInterface $product,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $product->getFamily()->willReturn($family);
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $this->isEditable($product, $attribute)->shouldReturn(true);
    }

    function it_throws_an_exception_if_the_variant_product_has_no_family_variant(
        VariantProductInterface $product,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getFamilyVariant()->willReturn(null);

        $this->shouldThrow(\Exception::class)->during('isEditable', [$product, $attribute]);
    }

    function it_throws_an_exception_if_the_product_model_has_no_family_variant(
        ProductModelInterface $productModel,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $productModel->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn(null);

        $this->shouldThrow(\Exception::class)->during('isEditable', [$productModel, $attribute]);
    }

    function it_throws_an_exception_if_the_family_variant_of_the_product_has_not_the_expected_variant_set(
        VariantProductInterface $product,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $product->getVariationLevel()->willReturn(1);
        $familyVariant->getVariantAttributeSet(1)->willReturn(null);

        $this->shouldThrow(\Exception::class)->during('isEditable', [$product, $attribute]);
    }

    function it_throws_an_exception_if_the_family_variant_of_the_product_model_has_not_the_expected_variant_set(
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $productModel->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getVariationLevel()->willReturn(1);
        $familyVariant->getVariantAttributeSet(1)->willReturn(null);

        $this->shouldThrow(\Exception::class)->during('isEditable', [$productModel, $attribute]);
    }

    function it_is_editable_if_it_is_a_variant_product_and_the_attribute_is_part_of_the_variant_set(
        VariantProductInterface $product,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attribute,
        VariantAttributeSetInterface $attributeSet
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getVariationLevel()->willReturn(1);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet);

        $attributeSet->hasAttribute($attribute)->willReturn(true);

        $this->isEditable($product, $attribute)->shouldReturn(true);
    }

    function it_is_not_editable_if_it_is_a_variant_product_and_the_attribute_is_not_part_of_the_variant_set(
        VariantProductInterface $product,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attribute,
        VariantAttributeSetInterface $attributeSet
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getVariationLevel()->willReturn(1);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet);

        $attributeSet->hasAttribute($attribute)->willReturn(false);

        $this->isEditable($product, $attribute)->shouldReturn(false);
    }

    function it_is_editable_if_it_is_a_product_model_and_the_attribute_is_part_of_the_variant_set(
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attribute,
        VariantAttributeSetInterface $attributeSet
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $productModel->getFamily()->willReturn($family);
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet);

        $attributeSet->hasAttribute($attribute)->willReturn(true);

        $this->isEditable($productModel, $attribute)->shouldReturn(true);
    }

    function it_is_not_editable_if_it_is_a_product_model_and_the_attribute_is_not_part_of_the_variant_set(
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attribute,
        VariantAttributeSetInterface $attributeSet
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $productModel->getFamily()->willReturn($family);
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet);

        $attributeSet->hasAttribute($attribute)->willReturn(false);

        $this->isEditable($productModel, $attribute)->shouldReturn(false);
    }

    function it_is_editable_if_it_is_a_root_product_model_and_the_attribute_is_part_of_the_common_attributes(
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attribute,
        CommonAttributeCollection $commonAttributes
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $productModel->getFamily()->willReturn($family);
        $productModel->getVariationLevel()->willReturn(0);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributes);

        $commonAttributes->contains($attribute)->willReturn(true);

        $this->isEditable($productModel, $attribute)->shouldReturn(true);
    }

    function it_is_not_editable_if_it_is_a_root_product_model_and_the_attribute_is_not_part_of_the_common_attributes(
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attribute,
        CommonAttributeCollection $commonAttributes
    ) {
        $attribute->getCode()->willReturn('code1');
        $family->hasAttributeCode(Argument::type('string'))->willReturn(true);
        $productModel->getFamily()->willReturn($family);
        $productModel->getVariationLevel()->willReturn(0);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributes);

        $commonAttributes->contains($attribute)->willReturn(false);

        $this->isEditable($productModel, $attribute)->shouldReturn(false);
    }
}
