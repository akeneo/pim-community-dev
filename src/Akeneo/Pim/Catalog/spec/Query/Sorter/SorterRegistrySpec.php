<?php

namespace spec\Pim\Component\Catalog\Query\Sorter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;

class SorterRegistrySpec extends ObjectBehavior
{
    function let(FieldSorterInterface $fieldSorter, AttributeSorterInterface $attributeSorter)
    {
        $this->register($fieldSorter);
        $this->register($attributeSorter);
    }

    function it_is_a_sorter_registry()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Sorter\SorterRegistryInterface');
    }

    function it_returns_a_supported_field_sorter($fieldSorter)
    {
        $fieldSorter->supportsField('field')->willReturn(true);
        $this->getFieldSorter('field')->shouldReturn($fieldSorter);
    }

    function it_returns_null_when_not_supported_field_sorter($fieldSorter)
    {
        $fieldSorter->supportsField('field')->willReturn(false);
        $this->getFieldSorter('field')->shouldReturn(null);
    }

    function it_returns_a_supported_attribute_sorter($attributeSorter, AttributeInterface $attribute)
    {
        $attributeSorter->supportsAttribute($attribute)->willReturn(true);
        $this->getAttributeSorter($attribute)->shouldReturn($attributeSorter);
    }

    function it_returns_null_when_not_supported_attribute_sorter($attributeSorter, AttributeInterface $attribute)
    {
        $attributeSorter->supportsAttribute($attribute)->willReturn(false);
        $this->getAttributeSorter($attribute)->shouldReturn(null);
    }
}
