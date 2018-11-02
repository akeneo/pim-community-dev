<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Query\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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
        $this->shouldImplement(FilterRegistryInterface::class);
    }

    function it_returns_a_supported_field_filter_and_operator($fieldFilter)
    {
        $fieldFilter->supportsField('field')->willReturn(true);
        $fieldFilter->supportsOperator('>')->willReturn(true);
        $this->getFieldFilter('field', '>')->shouldReturn($fieldFilter);
    }

    function it_returns_null_when_not_supported_field_filter_or_operator($fieldFilter)
    {
        $fieldFilter->supportsField('field')->willReturn(false);
        $fieldFilter->supportsOperator('>')->willReturn(true);
        $this->getFieldFilter('field', '>')->shouldReturn(null);

        $fieldFilter->supportsField('another_field')->willReturn(true);
        $fieldFilter->supportsOperator('>')->willReturn(false);
        $this->getFieldFilter('another_field', '>')->shouldReturn(null);
    }

    function it_returns_a_supported_attribute_filter_and_operator($attributeFilter, AttributeInterface $attribute)
    {
        $attributeFilter->supportsAttribute($attribute)->willReturn(true);
        $attributeFilter->supportsOperator('<')->willReturn(true);
        $this->getAttributeFilter($attribute, '<')->shouldReturn($attributeFilter);
    }

    function it_returns_null_when_not_supported_attribute_filter_or_operator(
        $attributeFilter,
        AttributeInterface $attribute,
        AttributeInterface $attribute2
    ) {
        $attributeFilter->supportsAttribute($attribute)->willReturn(false);
        $attributeFilter->supportsOperator('<')->willReturn(true);
        $this->getAttributeFilter($attribute, '<')->shouldReturn(null);

        $attributeFilter->supportsAttribute($attribute2)->willReturn(true);
        $attributeFilter->supportsOperator('<')->willReturn(false);
        $this->getAttributeFilter($attribute2, '<')->shouldReturn(null);
    }

    function it_returns_a_supported_filter($attributeFilter, $attributeRepository, AttributeInterface $attribute)
    {
        $attributeRepository->findOneBy(Argument::any())->willReturn($attribute);

        $attributeFilter->supportsAttribute($attribute)->willReturn(true);
        $attributeFilter->supportsOperator('EMPTY')->willReturn(true);
        $this->getFilter('name', 'EMPTY')->shouldReturn($attributeFilter);
    }

    function it_returns_field_filters($fieldFilter)
    {
        $this->getFieldFilters()->shouldReturn([$fieldFilter]);
    }

    function it_returns_attribute_filters($attributeFilter)
    {
        $this->getAttributeFilters()->shouldReturn([$attributeFilter]);
    }
}
