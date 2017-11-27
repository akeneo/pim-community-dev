<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Value\ScalarValue;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Prophecy\Argument;

class MergeDataOnProductSpec extends ObjectBehavior
{
    function let(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $associationMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith([$valuesMerger, $associationMerger, $categoryMerger], $attributeRepository);
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
        AttributeInterface $identifierAttribute,
        ArrayCollection $groups,
        ArrayCollection $uniqueData
    ) {
        $filteredProduct->getId()->willReturn(1);
        $filteredProduct->isEnabled()->willReturn(true);
        $filteredProduct->getFamily()->willReturn($family);
        $filteredProduct->getFamilyId()->willReturn(2);
        $filteredProduct->getIdentifier()->willReturn('my_sku');
        $filteredProduct->getGroups()->willReturn($groups);
        $filteredProduct->getUniqueData()->willReturn($uniqueData);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $filteredProduct->getValue('sku')->willReturn($identifierValue);
        $identifierValue->getAttribute()->willReturn($identifierAttribute);

        $valuesMerger->merge($filteredProduct, $fullProduct)->willReturn($fullProduct);
        $associationMerger->merge($filteredProduct, $fullProduct)->willReturn($fullProduct);
        $categoryMerger->merge($filteredProduct, $fullProduct)->willReturn($fullProduct);

        $fullProduct->setEnabled(true)->shouldBeCalled();
        $fullProduct->setFamily($family)->shouldBeCalled();
        $fullProduct->setFamilyId(2)->shouldBeCalled();
        $fullProduct->setIdentifier(Argument::type(ScalarValue::class))->shouldBeCalled();
        $fullProduct->setGroups($groups)->shouldBeCalled();
        $fullProduct->setUniqueData($uniqueData)->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_sets_parent_from_unit_of_work_if_it_is_a_variant_product_and_if_it_is_new(
        $productModelRepository,
        VariantProductInterface $filteredVariantProduct,
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
        $productModelRepository,
        $attributeRepository,
        $addParent,
        VariantProductInterface $filteredVariantProduct,
        ProductInterface $fullProduct,
        VariantProductInterface $fullVariantProduct,
        ProductModelInterface $parent,
        ProductModelInterface $parentInUoW,
        $valuesMerger,
        $associationMerger,
        $categoryMerger,
        FamilyInterface $family,
        ValueInterface $identifierValue,
        AttributeInterface $identifierAttribute,
        ArrayCollection $groups,
        ArrayCollection $uniqueData
    ) {
        $parent->getId()->willReturn(1);
        $parent->getCode()->willReturn('parent_code');
        $filteredVariantProduct->getParent()->willReturn($parent);
        $productModelRepository->find(1)->willReturn($parentInUoW);

        $filteredVariantProduct->setParent($parentInUoW)->shouldBeCalled();

        $filteredVariantProduct->getId()->willReturn(1);
        $filteredVariantProduct->isEnabled()->willReturn(true);
        $filteredVariantProduct->getFamily()->willReturn($family);
        $filteredVariantProduct->getFamilyId()->willReturn(2);
        $filteredVariantProduct->getIdentifier()->willReturn('my_sku');
        $filteredVariantProduct->getGroups()->willReturn($groups);
        $filteredVariantProduct->getUniqueData()->willReturn($uniqueData);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $filteredVariantProduct->getValue('sku')->willReturn($identifierValue);
        $identifierValue->getAttribute()->willReturn($identifierAttribute);

        $fullProduct->setEnabled(true)->shouldBeCalled();
        $fullProduct->setFamily($family)->shouldBeCalled();
        $fullProduct->setFamilyId(2)->shouldBeCalled();
        $fullProduct->setIdentifier(Argument::type(ScalarValue::class))->shouldBeCalled();
        $fullProduct->setGroups($groups)->shouldBeCalled();
        $fullProduct->setUniqueData($uniqueData)->shouldBeCalled();

        $addParent->to($fullProduct, 'parent_code')->willReturn($fullVariantProduct);

        $valuesMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);
        $associationMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);
        $categoryMerger->merge($filteredVariantProduct, $fullVariantProduct)->willReturn($fullVariantProduct);

        $this->merge($filteredVariantProduct, $fullProduct)->shouldReturn($fullVariantProduct);
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
