<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use PhpSpec\ObjectBehavior;

class NumberComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_number']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf(ComparatorInterface::class);
    }

    function it_supports_comparison()
    {
        $this->supports('pim_catalog_number')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_value()
    {
        $changes = ['data' => '10', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_value()
    {
        $changes = ['data' => '10', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => '11', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_values_are_the_same_with_string()
    {
        $changes = ['data' => '10', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 10, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_returns_null_when_values_are_the_same_as_integer()
    {
        $changes = ['data' => 10, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 10, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_returns_null_when_values_are_the_same_as_float()
    {
        $changes = ['data' => 10.00, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => '10', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_returns_changes_if_it_compares_0_to_null()
    {
        $changes = ['data' => 0, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => null, 'locale' => null, 'scope' => null];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }
}
