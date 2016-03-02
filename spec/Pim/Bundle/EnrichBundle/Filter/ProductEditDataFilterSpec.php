<?php

namespace spec\Pim\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class ProductEditDataFilterSpec extends ObjectBehavior
{
    function let(
        SecurityFacade $securityFacade,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->beConstructedWith(
            $securityFacade,
            $collectionFilter
        );
    }

    function it_filters_non_values_data_when_not_granted($securityFacade, ProductInterface $product, $collectionFilter)
    {
        $data = [
            'family'        => 'some family',
            'groups'        => [],
            'categories'    => ['lexmark'],
            'enabled'       => true,
            'associations'  => [],
            'values'        => []
        ];

        $collectionFilter->filterCollection([], 'pim.internal_api.product_values_data.edit')->willReturn([]);
        $securityFacade->isGranted(Argument::any())->willReturn(false);

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn(['values' => []]);
    }

    function it_does_not_filters_non_values_data_when_granted($securityFacade, ProductInterface $product, $collectionFilter)
    {
        $data = [
            'family'        => 'some family',
            'groups'        => [],
            'categories'    => ['lexmark'],
            'enabled'       => true,
            'associations'  => [],
            'values'        => []
        ];

        $collectionFilter->filterCollection([], 'pim.internal_api.product_values_data.edit')->willReturn([]);
        $securityFacade->isGranted(Argument::any())->willReturn(true);

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn($data);
    }
}
