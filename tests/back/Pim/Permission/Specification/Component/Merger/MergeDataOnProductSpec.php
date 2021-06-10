<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Prophecy\Argument;

class MergeDataOnProductSpec extends ObjectBehavior
{
    function let(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $associationMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        AttributeRepositoryInterface $attributeRepository,
        AddParent $addParent,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith(
            [$valuesMerger, $associationMerger, $categoryMerger],
            $attributeRepository,
            $addParent,
            $productModelRepository
        );
    }

    function it_return_filtered_product_when_it_is_new(ProductInterface $filteredProduct)
    {
        $this->merge($filteredProduct)->shouldReturn($filteredProduct);
    }

    function it_applies_values_from_filtered_product_to_full_product(
        $attributeRepository,
        $valuesMerger,
        $associationMerger,
        $categoryMerger,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        FamilyInterface $family,
        ValueInterface $identifierValue,
        ArrayCollection $groups,
        ArrayCollection $uniqueData,
        WriteValueCollection $productValues
    ) {
        $filteredProduct->getId()->willReturn(1);
        $filteredProduct->isEnabled()->willReturn(true);
        $filteredProduct->getFamily()->willReturn($family);
        $filteredProduct->getFamilyId()->willReturn(2);
        $filteredProduct->getIdentifier()->willReturn('my_sku');
        $filteredProduct->getGroups()->willReturn($groups);
        $filteredProduct->getUniqueData()->willReturn($uniqueData);
        $filteredProduct->isVariant()->willReturn(false);
        $filteredProduct->getParent()->willReturn(null);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $fullProduct->getValue('sku')->willReturn($identifierValue);
        $identifierValue->getAttributeCode()->willReturn('sku');

        $valuesMerger->merge($filteredProduct, $fullProduct)->willReturn($fullProduct);
        $associationMerger->merge($filteredProduct, $fullProduct)->willReturn($fullProduct);
        $categoryMerger->merge($filteredProduct, $fullProduct)->willReturn($fullProduct);

        $fullProduct->setEnabled(true)->shouldBeCalled();
        $fullProduct->setFamily($family)->shouldBeCalled();
        $fullProduct->setFamilyId(2)->shouldBeCalled();
        $fullProduct->getValues()->willReturn($productValues);
        $productValues->removeByAttributeCode('sku')->shouldBeCalled();
        $fullProduct->addValue(Argument::type(ScalarValue::class))->shouldBeCalled();
        $fullProduct->setIdentifier('my_sku')->shouldBeCalled();
        $fullProduct->setGroups($groups)->shouldBeCalled();
        $fullProduct->setUniqueData($uniqueData)->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_converts_the_full_product_to_a_variant_product(
        $productModelRepository,
        $attributeRepository,
        $addParent,
        $valuesMerger,
        $associationMerger,
        $categoryMerger,
        ProductInterface $filteredVariantProduct,
        ProductInterface $fullProduct,
        ProductInterface $fullVariantProduct,
        ProductModelInterface $parent,
        ProductModelInterface $parentInUoW,
        FamilyInterface $family,
        ValueInterface $identifierValue,
        ArrayCollection $groups,
        ArrayCollection $uniqueData,
        WriteValueCollection $productValues
    ) {
        $parent->getId()->willReturn(1);
        $parent->getCode()->willReturn('parent_code');
        $filteredVariantProduct->getParent()->willReturn($parent);
        $productModelRepository->find(1)->willReturn($parentInUoW);

        $filteredVariantProduct->getId()->willReturn(1);
        $filteredVariantProduct->isEnabled()->willReturn(true);
        $filteredVariantProduct->getFamily()->willReturn($family);
        $filteredVariantProduct->getFamilyId()->willReturn(2);
        $filteredVariantProduct->getIdentifier()->willReturn('my_sku');
        $filteredVariantProduct->getGroups()->willReturn($groups);
        $filteredVariantProduct->getUniqueData()->willReturn($uniqueData);
        $filteredVariantProduct->isVariant()->willReturn(true);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $fullProduct->getValue('sku')->willReturn($identifierValue);
        $identifierValue->getAttributeCode()->willReturn('sku');

        $fullProduct->setEnabled(true)->shouldBeCalled();
        $fullProduct->setFamily($family)->shouldBeCalled();
        $fullProduct->setFamilyId(2)->shouldBeCalled();
        $fullProduct->getValues()->willReturn($productValues);
        $productValues->removeByAttributeCode('sku')->shouldBeCalled();
        $fullProduct->addValue(Argument::type(ScalarValue::class))->shouldBeCalled();
        $fullProduct->setIdentifier('my_sku')->shouldBeCalled();
        $fullProduct->setGroups($groups)->shouldBeCalled();
        $fullProduct->setUniqueData($uniqueData)->shouldBeCalled();
        $fullProduct->isVariant()->willReturn(false);

        $addParent->to($fullProduct, 'parent_code')->willReturn($fullVariantProduct);

        $valuesMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);
        $associationMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);
        $categoryMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);

        $this->merge($filteredVariantProduct, $fullProduct)->shouldReturn($fullVariantProduct);
    }

    function it_merges_values_even_if_identifier_is_not_granted(
        $attributeRepository,
        $valuesMerger,
        $associationMerger,
        $categoryMerger,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        FamilyInterface $family,
        ValueInterface $identifierValue,
        ArrayCollection $groups,
        ArrayCollection $uniqueData,
        WriteValueCollection $productValues
    ) {
        $filteredProduct->getId()->willReturn(1);
        $filteredProduct->isEnabled()->willReturn(true);
        $filteredProduct->getFamily()->willReturn($family);
        $filteredProduct->getFamilyId()->willReturn(2);
        $filteredProduct->getIdentifier()->willReturn('my_sku');
        $filteredProduct->getGroups()->willReturn($groups);
        $filteredProduct->getUniqueData()->willReturn($uniqueData);
        $filteredProduct->isVariant()->willReturn(false);
        $filteredProduct->getParent()->willReturn(null);

        $filteredProduct->getValue('sku')->willReturn(null);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $fullProduct->getValue('sku')->willReturn($identifierValue);
        $fullProduct->isVariant()->willReturn(false);
        $identifierValue->getAttributeCode()->willReturn('sku');

        $valuesMerger->merge($filteredProduct, $fullProduct)->willReturn($fullProduct);
        $associationMerger->merge($filteredProduct, $fullProduct)->willReturn($fullProduct);
        $categoryMerger->merge($filteredProduct, $fullProduct)->willReturn($fullProduct);

        $fullProduct->setEnabled(true)->shouldBeCalled();
        $fullProduct->setFamily($family)->shouldBeCalled();
        $fullProduct->setFamilyId(2)->shouldBeCalled();
        $fullProduct->getValues()->willReturn($productValues);
        $productValues->removeByAttributeCode('sku')->shouldBeCalled();
        $fullProduct->addValue(Argument::type(ScalarValue::class))->shouldBeCalled();
        $fullProduct->setIdentifier('my_sku')->shouldBeCalled();
        $fullProduct->setGroups($groups)->shouldBeCalled();
        $fullProduct->setUniqueData($uniqueData)->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_changes_the_parent_of_a_variant_product(
        $productModelRepository,
        $attributeRepository,
        $valuesMerger,
        $associationMerger,
        $categoryMerger,
        ProductInterface $filteredVariantProduct,
        ProductInterface $fullVariantProduct,
        ProductModelInterface $parent,
        ProductModelInterface $parentInUoW,
        FamilyInterface $family,
        ValueInterface $identifierValue,
        ArrayCollection $groups,
        ArrayCollection $uniqueData,
        FamilyVariantInterface $familyVariant,
        WriteValueCollection $variantProductValues
    ) {
        $filteredVariantProduct->getParent()->willReturn($parent);
        $parent->getId()->willReturn(1);
        $productModelRepository->find(1)->willReturn($parentInUoW);

        $filteredVariantProduct->getId()->willReturn(1);
        $filteredVariantProduct->isEnabled()->willReturn(true);
        $filteredVariantProduct->getFamily()->willReturn($family);
        $filteredVariantProduct->getFamilyId()->willReturn(2);
        $filteredVariantProduct->getIdentifier()->willReturn('my_sku');
        $filteredVariantProduct->getGroups()->willReturn($groups);
        $filteredVariantProduct->getUniqueData()->willReturn($uniqueData);
        $filteredVariantProduct->getFamilyVariant()->willReturn($familyVariant);
        $filteredVariantProduct->isVariant()->willReturn(true);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $fullVariantProduct->getValue('sku')->willReturn($identifierValue);
        $identifierValue->getAttributeCode()->willReturn('sku');

        $fullVariantProduct->isVariant()->willReturn(true);
        $fullVariantProduct->setEnabled(true)->shouldBeCalled();
        $fullVariantProduct->setFamily($family)->shouldBeCalled();
        $fullVariantProduct->setFamilyId(2)->shouldBeCalled();
        $fullVariantProduct->getValues()->willReturn($variantProductValues);
        $variantProductValues->removeByAttributeCode('sku')->shouldBeCalled();
        $fullVariantProduct->addValue(Argument::type(ScalarValue::class))->shouldBeCalled();
        $fullVariantProduct->setIdentifier('my_sku')->shouldBeCalled();
        $fullVariantProduct->setGroups($groups)->shouldBeCalled();
        $fullVariantProduct->setUniqueData($uniqueData)->shouldBeCalled();
        $fullVariantProduct->setFamilyVariant($familyVariant)->shouldBeCalled();
        $fullVariantProduct->setParent($parentInUoW)->shouldBeCalled();

        $valuesMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);
        $associationMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);
        $categoryMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);

         $this->merge($filteredVariantProduct, $fullVariantProduct);
    }

    function it_throws_an_exception_if_filtered_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), ProductInterface::class))
            ->during('merge', [new \stdClass(), new Product()]);
    }

    function it_throws_an_exception_if_full_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), ProductInterface::class))
            ->during('merge', [new Product(), new \stdClass()]);
    }
}
