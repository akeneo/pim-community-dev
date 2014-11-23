<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Query;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeSorterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class QuerySorterRegistrySpec extends ObjectBehavior
{
    function let(FieldSorterInterface $fieldSorter, AttributeSorterInterface $attributeSorter)
    {
        $this->register($fieldSorter);
        $this->register($attributeSorter);
    }

    function it_is_a_sorter_registry()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\QuerySorterRegistryInterface');
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
