<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class ProductValuesEditDataFilterSpec extends ObjectBehavior
{
    function it_filters_values_data_on_attributes_read_only_true(AttributeInterface $attribute)
    {
        $attribute->getProperty('is_read_only')->willReturn(true);
        $this->filterObject($attribute, '', [])->shouldReturn(true);
    }

    function it_filters_values_data_on_attributes_read_only_false(AttributeInterface $attribute)
    {
        $attribute->getProperty('is_read_only')->willReturn(false);
        $this->filterObject($attribute, '', [])->shouldReturn(false);
    }

    function it_should_support_attribute(AttributeInterface $attribute)
    {
        $this->supportsObject($attribute, '', [])->shouldReturn(true);
    }

    function it_should_fail_when_object_is_not_an_attribute(ProductInterface $product)
    {
        $this->supportsObject($product, '', [])->shouldReturn(false);
    }
}
