<?php

declare(strict_types=1);

namespace spec\Akeneo\Pim\Permission\Component\Factory;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Prophecy\Argument;

class FilteredEntityFactorySpec extends ObjectBehavior
{
    function let(
        NotGrantedDataFilterInterface $categoryFilter,
        NotGrantedDataFilterInterface $valuesFilter,
        NotGrantedDataFilterInterface $associationFilter
    ) {
        $this->beConstructedWith([$categoryFilter, $valuesFilter, $associationFilter]);
    }

    function it_creates_a_filtered_product(
        $categoryFilter,
        $valuesFilter,
        $associationFilter,
        ProductInterface $fullProduct,
        ProductInterface $filteredProduct
    ) {
        $categoryFilter->filter($fullProduct)->willReturn($filteredProduct);
        $valuesFilter->filter($filteredProduct)->willReturn($filteredProduct);
        $associationFilter->filter($filteredProduct)->willReturn($filteredProduct);

        $this->create($fullProduct)->shouldReturn($filteredProduct);
    }

    function it_creates_a_filtered_product_model(
        $categoryFilter,
        $valuesFilter,
        $associationFilter,
        ProductModelInterface $fullProductModel,
        ProductModelInterface $filteredProductModel
    ) {
        $categoryFilter->filter($fullProductModel)->willReturn($filteredProductModel);
        $valuesFilter->filter($filteredProductModel)->willReturn($filteredProductModel);
        $associationFilter->filter($filteredProductModel)->willReturn($filteredProductModel);

        $this->create($fullProductModel)->shouldReturn($filteredProductModel);
    }

    function it_throws_an_exception_if_a_filter_does_not_respect_the_interface(ProductInterface $fullProduct)
    {
        $this->beConstructedWith([new \stdClass()]);

        $this->shouldThrow(
            InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), NotGrantedDataFilterInterface::class)
        )->duringCreate($fullProduct);
    }
}
