<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\ProductModel;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\VariantProductRepositoryInterface;

class ImageAsLabelSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        VariantProductRepositoryInterface $productRepository
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

        $this->value($productModel)->shouldReturn($imageValue);
    }

    function it_gets_the_attribute_as_image_value_of_a_product_model_coming_from_a_parent(
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

        $this->value($productModel)->shouldReturn($imageValue);
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
            ['created' => 'ASC', 'code' => 'ASC'],
            1
        )->willReturn([$subProductModel]);

        $productRepository->findLastCreatedByParent($productModel)->willReturn(null);

        $subProductModel->getImage()->willReturn($imageValue);

        $this->value($productModel)->shouldReturn($imageValue);
    }

    function it_gets_the_attribute_as_image_value_of_a_product_model_coming_from_a_variant_product_child(
        $productModelRepository,
        $productRepository,
        ProductModelInterface $productModel,
        ProductInterface $variantProduct,
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
            ['created' => 'ASC', 'code' => 'ASC'],
            1
        )->willReturn([]);

        $productRepository->findLastCreatedByParent($productModel)->willReturn($variantProduct);

        $variantProduct->getImage()->willReturn($imageValue);

        $this->value($productModel)->shouldReturn($imageValue);
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
        Collection $attributeCollectionTwo
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
            ['created' => 'ASC', 'code' => 'ASC'],
            1
        )->willReturn([]);

        $productRepository->findLastCreatedByParent($productModel)->willReturn(null);

        $this->value($productModel)->shouldReturn(null);
    }
}
