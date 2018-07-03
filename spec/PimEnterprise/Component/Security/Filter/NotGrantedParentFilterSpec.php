<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\Security\Filter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Factory\FilteredEntityFactory;
use PimEnterprise\Component\Security\Filter\NotGrantedParentFilter;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;

class NotGrantedParentFilterSpec extends ObjectBehavior
{
    function let(FilteredEntityFactory $filteredProductModelFactory)
    {
        $this->beConstructedWith($filteredProductModelFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotGrantedParentFilter::class);
        $this->shouldImplement(NotGrantedDataFilterInterface::class);
    }

    function it_does_not_filter_an_entity_without_family_variant(ProductInterface $product)
    {
        $this->filter($product)->shouldBeLike($product);
    }

    function it_does_not_filter_an_entity_with_family_variant_but_no_parent(ProductInterface $product)
    {
        $product->getParent()->willReturn(null);

        $this->filter($product)->shouldBeAnInstanceOf(ProductInterface::class);
    }

    function it_filters_an_entity_with_family_variant(
        $filteredProductModelFactory,
        ProductInterface $product,
        ProductModelInterface $parent,
        ProductModelInterface $filteredParent
    ) {
        $product->getParent()->willReturn($parent);

        $filteredProductModelFactory
            ->create($parent)
            ->shouldBeCalled()
            ->willReturn($filteredParent);
        $product->setParent($filteredParent)->shouldBeCalled();

        $filteredProduct = $this->filter($product);
        $filteredProduct->shouldBeAnInstanceOf(ProductInterface::class);
    }
}
