<?php

namespace spec\Pim\Bundle\CatalogBundle\Query\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class FilterRegistrySpec extends ObjectBehavior
{
    function let(
        FieldFilterInterface $fieldFilter,
        AttributeFilterInterface $attributeFilter,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($attributeRepository);
        $this->register($fieldFilter);
        $this->register($attributeFilter);
    }

    function it_is_a_filter_registry()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\FilterRegistryInterface');
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

    function it_returns_a_supported_filter($attributeFilter, $attributeRepository, AttributeInterface $attribute)
    {
        $attributeRepository->findOneBy(Argument::any())->willReturn($attribute);

        $attributeFilter->supportsAttribute($attribute)->willReturn(true);
        $this->getAttributeFilter($attribute)->shouldReturn($attributeFilter);
    }
}
