<?php

namespace spec\Pim\Component\Catalog\Comparator\Attribute;

use PhpSpec\ObjectBehavior;

class BooleanComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_boolean']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\ComparatorInterface');
    }

    function it_supports_comparison()
    {
        $this->supports('pim_catalog_boolean')->shouldBe(true);
    }

    function it_gets_changes_when_adding_value()
    {
        $changes = ['data' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn($changes);

        $changes = ['data' => 1, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_gets_changes_when_changing_value()
    {
        $changes   = ['data' => false, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn($changes);

        $changes   = ['data' => 0, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_values_are_the_same()
    {
        $changes   = ['data' => 1, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);

        $changes   = ['data' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }
}
