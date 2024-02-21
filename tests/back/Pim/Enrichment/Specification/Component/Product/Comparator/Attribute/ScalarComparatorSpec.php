<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use PhpSpec\ObjectBehavior;

class ScalarComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'pim_catalog_date',
            'pim_catalog_identifier',
            'pim_catalog_text',
            'pim_catalog_textarea'
        ]);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf(ComparatorInterface::class);
    }

    function it_supports_comparison()
    {
        $this->supports('pim_catalog_date')->shouldBe(true);
        $this->supports('pim_catalog_identifier')->shouldBe(true);
        $this->supports('pim_catalog_text')->shouldBe(true);
        $this->supports('pim_catalog_textarea')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_value()
    {
        $changes = ['data' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_value()
    {
        $changes = ['data' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 'other scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_values_are_the_same()
    {
        $changes = ['data' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_returns_value_when_value_is_array()
    {
        $changes = ['data' => ['scalar'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_value_when_value_is_integer()
    {
        $changes = ['data' => 2, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_value_when_value_is_float()
    {
        $changes = ['data' => 2.44, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_value_when_values_are_null()
    {
        $changes = null;
        $originals = ['data' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }
}
