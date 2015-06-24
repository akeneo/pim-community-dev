<?php

namespace spec\Pim\Component\Catalog\Comparator\Attribute;

use PhpSpec\ObjectBehavior;

class OptionComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\ComparatorInterface');
    }

    function it_supports_simpleselect_type()
    {
        $this->supports('pim_catalog_simpleselect')->shouldBe(true);
        $this->supports('pim_reference_data_simpleselect')->shouldBe(true);
    }

    function it_get_changes_when_adding_option_data()
    {
        $changes   = ['value' => '42', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn([
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
            'value'  => '42',
        ]);
    }

    function it_get_changes_when_changing_option_data()
    {
        $changes   = ['value' => '42', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => '40'];

        $this->compare($changes, $originals)->shouldReturn([
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
            'value'  => '42',
        ]);
    }

    function it_returns_null_when_option_is_the_same()
    {
        $changes   = ['value' => '42', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => '42', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }
}
