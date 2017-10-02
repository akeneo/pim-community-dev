<?php

namespace spec\Pim\Component\Catalog\ProductModel;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class ProductModelImageAsLabelSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($productModelRepository, $productRepository);
    }

    function it_gets_the_own_attribute_as_image_value_of_a_product_model(
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attributeAsImage,
        Collection $attributeSets,
        \ArrayIterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        Collection $attributeCollectionOne,
        Collection $attributeCollectionTwo,
        ValueInterface $imageValue
    ) {
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        $attributeSetOne->getAttributes()->willReturn($attributeCollectionOne);
        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetTwo->getAttributes()->willReturn($attributeCollectionTwo);
        $attributeSetTwo->getLevel()->willReturn(2);

        $attributeCollectionOne->contains($attributeAsImage)->willReturn(true);
        $attributeCollectionTwo->contains($attributeAsImage)->willReturn(false);

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);
        $productModel->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getLevel()->willReturn(1);
        $productModel->getImage()->willReturn($imageValue);

        $this->getImage($productModel)->shouldReturn($imageValue);
    }

    function it_gets_the_attribute_as_image_value_of_a_product_model_coming_from_a_parent(
        $productModelRepository,
        $productRepository,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attributeAsImage,
        Collection $attributeSets,
        \ArrayIterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        Collection $attributeCollectionOne,
        Collection $attributeCollectionTwo,
        ValueInterface $imageValue
    ) {
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        $attributeSetOne->getAttributes()->willReturn($attributeCollectionOne);
        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetTwo->getAttributes()->willReturn($attributeCollectionTwo);
        $attributeSetTwo->getLevel()->willReturn(2);

        $attributeCollectionOne->contains($attributeAsImage)->willReturn(false);
        $attributeCollectionTwo->contains($attributeAsImage)->willReturn(false);

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);
        $productModel->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getLevel()->willReturn(1);
        $productModel->getImage()->willReturn($imageValue);

        $this->getImage($productModel)->shouldReturn($imageValue);
    }

    function it_gets_the_attribute_as_image_value_of_a_product_model_coming_from_a_product_model_child(
        $productModelRepository,
        $productRepository,
        ProductModelInterface $productModel,
        ProductModelInterface $subProductModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attributeAsImage,
        Collection $attributeSets,
        \ArrayIterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        Collection $attributeCollectionOne,
        Collection $attributeCollectionTwo,
        ValueInterface $imageValue
    ) {
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        $attributeSetOne->getAttributes()->willReturn($attributeCollectionOne);
        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetTwo->getAttributes()->willReturn($attributeCollectionTwo);
        $attributeSetTwo->getLevel()->willReturn(2);

        $attributeCollectionOne->contains($attributeAsImage)->willReturn(false);
        $attributeCollectionTwo->contains($attributeAsImage)->willReturn(true);

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);
        $productModel->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getLevel()->willReturn(1);

        $productModelRepository->findBy(
            ['parent' => $productModel],
            ['created' => 'DESC', 'code' => 'ASC'],
            1
        )->willReturn([$subProductModel]);

        $productRepository->findBy(
            ['parent' => $productModel],
            ['created' => 'DESC', 'identifier' => 'ASC'],
            1
        )->willReturn([]);

        $subProductModel->getImage()->willReturn($imageValue);

        $this->getImage($productModel)->shouldReturn($imageValue);
    }

    function it_gets_the_attribute_as_image_value_of_a_product_model_coming_from_a_variant_product_child(
        $productModelRepository,
        $productRepository,
        ProductModelInterface $productModel,
        VariantProductInterface $variantProduct,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attributeAsImage,
        Collection $attributeSets,
        \ArrayIterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        Collection $attributeCollectionOne,
        Collection $attributeCollectionTwo,
        ValueInterface $imageValue
    ) {
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        $attributeSetOne->getAttributes()->willReturn($attributeCollectionOne);
        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetTwo->getAttributes()->willReturn($attributeCollectionTwo);
        $attributeSetTwo->getLevel()->willReturn(2);

        $attributeCollectionOne->contains($attributeAsImage)->willReturn(false);
        $attributeCollectionTwo->contains($attributeAsImage)->willReturn(true);

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);
        $productModel->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getLevel()->willReturn(1);

        $productModelRepository->findBy(
            ['parent' => $productModel],
            ['created' => 'DESC', 'code' => 'ASC'],
            1
        )->willReturn([]);

        $productRepository->findBy(
            ['parent' => $productModel],
            ['created' => 'DESC', 'identifier' => 'ASC'],
            1
        )->willReturn([$variantProduct]);

        $variantProduct->getImage()->willReturn($imageValue);

        $this->getImage($productModel)->shouldReturn($imageValue);
    }

    function it_returns_null_if_no_image_available_anywhere(
        $productModelRepository,
        $productRepository,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $attributeAsImage,
        Collection $attributeSets,
        \ArrayIterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        Collection $attributeCollectionOne,
        Collection $attributeCollectionTwo,
        ValueInterface $imageValue
    ) {
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        $attributeSetOne->getAttributes()->willReturn($attributeCollectionOne);
        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetTwo->getAttributes()->willReturn($attributeCollectionTwo);
        $attributeSetTwo->getLevel()->willReturn(2);

        $attributeCollectionOne->contains($attributeAsImage)->willReturn(false);
        $attributeCollectionTwo->contains($attributeAsImage)->willReturn(true);

        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);
        $productModel->getFamily()->willReturn($family);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getLevel()->willReturn(1);

        $productModelRepository->findBy(
            ['parent' => $productModel],
            ['created' => 'DESC', 'code' => 'ASC'],
            1
        )->willReturn([]);

        $productRepository->findBy(
            ['parent' => $productModel],
            ['created' => 'DESC', 'identifier' => 'ASC'],
            1
        )->willReturn([]);

        $this->getImage($productModel)->shouldReturn(null);
    }
}
