<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Query;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class QueryFilterRegistrySpec extends ObjectBehavior
{
    function let(FieldFilterInterface $fieldFilter, AttributeFilterInterface $attributeFilter)
    {
        $this->register($fieldFilter);
        $this->register($attributeFilter);
    }

    function it_is_a_filter_registry()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\QueryFilterRegistryInterface');
    }

    function it_returns_a_supported_field_filter($fieldFilter)
    {
        $fieldFilter->supportsField('field')->willReturn(true);
        $this->getFieldFilter('field')->shouldReturn($fieldFilter);
    }

    function it_returns_null_when_not_supported_field_filter($fieldFilter)
    {
        $fieldFilter->supportsField('field')->willReturn(false);
        $this->getFieldFilter('field')->shouldReturn(null);
    }

    function it_returns_a_supported_attribute_filter($attributeFilter, AttributeInterface $attribute)
    {
        $attributeFilter->supportsAttribute($attribute)->willReturn(true);
        $this->getAttributeFilter($attribute)->shouldReturn($attributeFilter);
    }

    function it_returns_null_when_not_supported_attribute_filter($attributeFilter, AttributeInterface $attribute)
    {
        $attributeFilter->supportsAttribute($attribute)->willReturn(false);
        $this->getAttributeFilter($attribute)->shouldReturn(null);
    }
}
