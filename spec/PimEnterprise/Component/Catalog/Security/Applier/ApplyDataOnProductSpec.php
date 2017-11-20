<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\Catalog\Security\Applier;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;

class ApplyDataOnProductSpec extends ObjectBehavior
{
    function let(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $associationMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($valuesMerger, $associationMerger, $categoryMerger, $productRepository, $attributeRepository);
    }

    function it_applies_values_from_filtered_product_to_full_product(
        $productRepository,
        $attributeRepository,
        $valuesMerger,
        $associationMerger,
        $categoryMerger,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        FamilyInterface $family,
        ValueInterface $identifierValue,
        AttributeInterface $identifierAttribute
    ) {
        $filteredProduct->getId()->willReturn(1);
        $productRepository->find(1)->willReturn($fullProduct);
        $filteredProduct->isEnabled()->willReturn(true);
        $filteredProduct->getFamily()->willReturn($family);
        $filteredProduct->getFamilyId()->willReturn(2);
        $filteredProduct->getIdentifier()->willReturn('my_sku');

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $filteredProduct->getValue('sku')->willReturn($identifierValue);
        $identifierValue->getAttribute()->willReturn($identifierAttribute);

        $valuesMerger->merge($filteredProduct, $fullProduct)->shouldBeCalled();
        $associationMerger->merge($filteredProduct, $fullProduct)->shouldBeCalled();
        $categoryMerger->merge($filteredProduct, $fullProduct)->shouldBeCalled();

        $this->apply($filteredProduct)->shouldReturn($fullProduct);
    }
}
