<?php

namespace spec\Akeneo\Pim\Permission\Bundle\Filter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

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
