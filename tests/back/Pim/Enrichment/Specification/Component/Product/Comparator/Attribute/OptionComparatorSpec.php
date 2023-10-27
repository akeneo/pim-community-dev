<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use PhpSpec\ObjectBehavior;

class OptionComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_simpleselect', 'pim_reference_data_simpleselect']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf(ComparatorInterface::class);
    }

    function it_supports_simpleselect_type()
    {
        $this->supports('pim_catalog_simpleselect')->shouldBe(true);
        $this->supports('pim_reference_data_simpleselect')->shouldBe(true);
    }

    function it_get_changes_when_adding_option_data()
    {
        $changes = ['data' => '42', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => '42',
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }

    function it_get_changes_when_changing_option_data()
    {
        $changes = ['data' => '42', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => '40'];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => '42',
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }

    function it_returns_null_when_option_is_the_same()
    {
        $changes = ['data' => '42', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => '42', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);

        $changes = ['data' => null, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => null, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_compares_in_a_case_insensitive_way(): void
    {
        $changes = ['data' => 'UPPER_CASE', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 'upper_case', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_returns_a_change_when_data_is_not_a_string(): void
    {
        $changes = ['data' => ['toto'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        // Originals cannot be anything other than a string, but this is for the purpose of the test
        $originals = ['data' => ['toto']];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => ['toto'],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);

        $changes = ['data' => 42, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 42];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => 42,
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }
}
