<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MergeDataOnProductSpec extends ObjectBehavior
{
    function let(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $associationMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        AttributeRepositoryInterface $attributeRepository,
        AddParent $addParent,
        RemoveParentInterface $removeParent,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith(
            [$valuesMerger, $associationMerger, $categoryMerger],
            $attributeRepository,
            $addParent,
            $removeParent,
            $productModelRepository
        );
    }

    function it_return_filtered_product_when_it_is_new(ProductInterface $filteredProduct)
    {
        $this->merge($filteredProduct)->shouldReturn($filteredProduct);
    }

    function it_applies_values_from_filtered_product_to_full_product(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $associationMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        AttributeRepositoryInterface $attributeRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        FamilyInterface $family,
        ValueInterface $formerIdentifierValue,
        Collection  $groups,
        Collection $uniqueData,
        WriteValueCollection $productValues
    ) {
        $filteredProduct->getId()->willReturn(1);
        $filteredProduct->isEnabled()->willReturn(true);
        $filteredProduct->getFamily()->willReturn($family);
        $filteredProduct->getIdentifier()->willReturn('my_sku');
        $filteredProduct->getGroups()->willReturn($groups);
        $filteredProduct->getUniqueData()->willReturn($uniqueData);
        $filteredProduct->isVariant()->willReturn(false);
        $filteredProduct->getParent()->willReturn(null);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $fullProduct->getValues()->willReturn($productValues);
        $productValues->getSame(Argument::type(ScalarValue::class))->willReturn($formerIdentifierValue);
        $formerIdentifierValue->isEqual(Argument::type(ScalarValue::class))->willReturn(true);

        $fullProduct->setEnabled(true)->shouldBeCalled();
        $fullProduct->setFamily($family)->shouldBeCalled();
        $fullProduct->addValue(Argument::type(ScalarValue::class))->shouldBeCalled();
        $fullProduct->setIdentifier('my_sku')->shouldBeCalled();
        $fullProduct->isVariant()->willReturn(false);
        $fullProduct->setGroups($groups)->shouldBeCalled();
        $fullProduct->setUniqueData($uniqueData)->shouldBeCalled();

        $valuesMerger->merge($filteredProduct, $fullProduct)->shouldBeCalled()->willReturn($fullProduct);
        $associationMerger->merge($filteredProduct, $fullProduct)->shouldBeCalled()->willReturn($fullProduct);
        $categoryMerger->merge($filteredProduct, $fullProduct)->shouldBeCalled()->willReturn($fullProduct);

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_sets_parent_from_unit_of_work_if_it_is_a_variant_product_and_if_it_is_new(
        ProductModelRepositoryInterface $productModelRepository,
        ProductInterface $filteredVariantProduct,
        ProductModelInterface $parent,
        ProductModelInterface $parentInUoW
    ) {
        $parent->getId()->willReturn(1);
        $filteredVariantProduct->getParent()->willReturn($parent);
        $productModelRepository->find(1)->willReturn($parentInUoW);

        $filteredVariantProduct->setParent($parentInUoW)->shouldBeCalled();

        $this->merge($filteredVariantProduct)->shouldReturn($filteredVariantProduct);
    }

    function it_converts_the_full_product_to_a_variant_product(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $associationMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        AttributeRepositoryInterface $attributeRepository,
        AddParent $addParent,
        ProductModelRepositoryInterface $productModelRepository,
        ProductInterface $filteredVariantProduct,
        ProductInterface $fullProduct,
        ProductInterface $fullVariantProduct,
        ProductModelInterface $parent,
        ProductModelInterface $parentInUoW,
        FamilyInterface $family,
        ValueInterface $identifierValue,
        Collection $groups,
        Collection $uniqueData,
        WriteValueCollection $productValues
    ) {
        $parent->getId()->willReturn(1);
        $parent->getCode()->willReturn('parent_code');
        $filteredVariantProduct->getParent()->willReturn($parent);
        $productModelRepository->find(1)->willReturn($parentInUoW);

        $filteredVariantProduct->setParent($parentInUoW)->shouldBeCalled();

        $filteredVariantProduct->getId()->willReturn(1);
        $filteredVariantProduct->isEnabled()->willReturn(true);
        $filteredVariantProduct->getFamily()->willReturn($family);
        $filteredVariantProduct->getIdentifier()->willReturn('my_sku');
        $filteredVariantProduct->getGroups()->willReturn($groups);
        $filteredVariantProduct->getUniqueData()->willReturn($uniqueData);
        $filteredVariantProduct->isVariant()->willReturn(true);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $fullProduct->getValues()->willReturn($productValues);
        $productValues->getSame(Argument::type(ScalarValue::class))->willReturn($identifierValue);
        $identifierValue->isEqual(Argument::type(ScalarValue::class))->willReturn(true);

        $fullProduct->setEnabled(true)->shouldBeCalled();
        $fullProduct->setFamily($family)->shouldBeCalled();
        $fullProduct->addValue(Argument::type(ScalarValue::class))->shouldBeCalled();
        $fullProduct->setIdentifier('my_sku')->shouldBeCalled();
        $fullProduct->setGroups($groups)->shouldBeCalled();
        $fullProduct->setUniqueData($uniqueData)->shouldBeCalled();
        $fullProduct->isVariant()->willReturn(false);

        $addParent->to($fullProduct, 'parent_code')->shouldBeCalled()->willReturn($fullVariantProduct);

        $valuesMerger->merge($filteredVariantProduct, $fullVariantProduct)->shouldBeCalled()->willReturn($fullVariantProduct);
        $associationMerger->merge($filteredVariantProduct, $fullVariantProduct)->shouldBeCalled()->willReturn($fullVariantProduct);
        $categoryMerger->merge($filteredVariantProduct, $fullVariantProduct)->shouldBeCalled()->willReturn($fullVariantProduct);

        $this->merge($filteredVariantProduct, $fullProduct)->shouldReturn($fullVariantProduct);
    }

    function it_changes_the_parent_of_a_variant_product(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $associationMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        AttributeRepositoryInterface $attributeRepository,
        ProductModelRepositoryInterface $productModelRepository,
        ProductInterface $filteredVariantProduct,
        ProductInterface $fullVariantProduct,
        ProductModelInterface $parent,
        ProductModelInterface $parentInUoW,
        FamilyInterface $family,
        ValueInterface $identifierValue,
        Collection $groups,
        Collection $uniqueData,
        FamilyVariantInterface $familyVariant,
        WriteValueCollection $variantProductValues
    ) {
        $filteredVariantProduct->getParent()->willReturn($parent);
        $parent->getId()->willReturn(1);
        $productModelRepository->find(1)->willReturn($parentInUoW);
        $filteredVariantProduct->setParent($parentInUoW)->shouldBeCalled();

        $filteredVariantProduct->getId()->willReturn(1);
        $filteredVariantProduct->isEnabled()->willReturn(true);
        $filteredVariantProduct->getFamily()->willReturn($family);
        $filteredVariantProduct->getIdentifier()->willReturn('my_sku');
        $filteredVariantProduct->getGroups()->willReturn($groups);
        $filteredVariantProduct->getUniqueData()->willReturn($uniqueData);
        $filteredVariantProduct->getFamilyVariant()->willReturn($familyVariant);
        $filteredVariantProduct->isVariant()->willReturn(true);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $fullVariantProduct->getValues()->willReturn($variantProductValues);
        $variantProductValues->getSame(Argument::type(ScalarValue::class))->willReturn($identifierValue);
        $identifierValue->isEqual(Argument::type(ScalarValue::class))->willReturn(false);

        $fullVariantProduct->isVariant()->willReturn(true);
        $fullVariantProduct->setEnabled(true)->shouldBeCalled();
        $fullVariantProduct->setFamily($family)->shouldBeCalled();
        $fullVariantProduct->getValues()->willReturn($variantProductValues);
        $fullVariantProduct->removeValue($identifierValue)->shouldBeCalled();
        $fullVariantProduct->addValue(Argument::type(ScalarValue::class))->shouldBeCalled();
        $fullVariantProduct->setIdentifier('my_sku')->shouldBeCalled();
        $fullVariantProduct->setGroups($groups)->shouldBeCalled();
        $fullVariantProduct->setUniqueData($uniqueData)->shouldBeCalled();
        $fullVariantProduct->setFamilyVariant($familyVariant)->shouldBeCalled();
        $fullVariantProduct->setParent(Argument::type(ProductModelInterface::class))->shouldBeCalled();

        $valuesMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);
        $associationMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);
        $categoryMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);

        $this->merge($filteredVariantProduct, $fullVariantProduct);
    }

    function it_removes_the_parent_of_a_variant_product(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $associationMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        AttributeRepositoryInterface $attributeRepository,
        RemoveParentInterface $removeParent,
        ProductInterface $filteredSimpleProduct,
        ProductInterface $fullVariantProduct,
        FamilyInterface $family,
        ArrayCollection $groups,
        ArrayCollection $uniqueData,
        WriteValueCollection $variantProductValues,
        ValueInterface $identifierValue
    ) {
        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $filteredSimpleProduct->getParent()->willReturn(null);
        $filteredSimpleProduct->getId()->willReturn(1);
        $filteredSimpleProduct->isEnabled()->willReturn(true);
        $filteredSimpleProduct->getFamily()->willReturn($family);
        $filteredSimpleProduct->getIdentifier()->willReturn('my_sku');
        $filteredSimpleProduct->getGroups()->willReturn($groups);
        $filteredSimpleProduct->getUniqueData()->willReturn($uniqueData);
        $filteredSimpleProduct->isVariant()->willReturn(false);

        $fullVariantProduct->getValues()->willReturn($variantProductValues);
        $variantProductValues->getSame(Argument::type(ScalarValue::class))->willReturn($identifierValue);
        $identifierValue->isEqual(Argument::type(ScalarValue::class))->willReturn(true);
        $fullVariantProduct->isVariant()->willReturn(true);

        $fullVariantProduct->setEnabled(true)->shouldBeCalled();
        $fullVariantProduct->setFamily($family)->shouldBeCalled();
        $fullVariantProduct->setGroups($groups)->shouldBeCalled();
        $fullVariantProduct->setUniqueData($uniqueData)->shouldBeCalled();
        $fullVariantProduct->addValue(Argument::type(ValueInterface::class))->shouldBeCalled();
        $fullVariantProduct->setIdentifier('my_sku')->shouldBeCalled();

        $removeParent->from($fullVariantProduct)->shouldBeCalled();

        $valuesMerger->merge($filteredSimpleProduct, $fullVariantProduct)->shouldBeCalled()->willReturn($fullVariantProduct);
        $associationMerger->merge($filteredSimpleProduct, $fullVariantProduct)->shouldBeCalled()->willReturn($fullVariantProduct);
        $categoryMerger->merge($filteredSimpleProduct, $fullVariantProduct)->shouldBeCalled()->willReturn($fullVariantProduct);

        $this->merge($filteredSimpleProduct, $fullVariantProduct);
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
