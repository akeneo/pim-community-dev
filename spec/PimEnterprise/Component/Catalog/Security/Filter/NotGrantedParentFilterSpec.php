<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\Catalog\Security\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use PimEnterprise\Component\Catalog\Security\Factory\FilteredEntityFactory;
use PimEnterprise\Component\Catalog\Security\Filter\NotGrantedParentFilter;
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
        $this->filter($product)->shouldReturn($product);
    }

    function it_does_not_filter_an_entity_with_family_variant_but_no_parent(VariantProductInterface $product)
    {
        $product->getParent()->willReturn(null);

        $this->filter($product)->shouldReturn($product);
    }

    function it_filters_an_entity_with_family_variant(
        $filteredProductModelFactory,
        VariantProductInterface $product,
        ProductModelInterface $parent,
        ProductModelInterface $filteredParent
    ) {
        $product->getParent()->willReturn($parent);

        $filteredProductModelFactory
            ->create($parent)
            ->shouldBeCalled()
            ->willReturn($filteredParent);
        $product->setParent($filteredParent)->shouldBeCalled();

        $this->filter($product)->shouldReturn($product);
    }
}
