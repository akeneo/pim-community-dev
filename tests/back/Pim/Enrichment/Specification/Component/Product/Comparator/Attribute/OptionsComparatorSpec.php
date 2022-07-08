<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use PhpSpec\ObjectBehavior;

class OptionsComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_multiselect', 'pim_reference_data_multiselect']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf(ComparatorInterface::class);
    }

    function it_supports_multiselect_type()
    {
        $this->supports('pim_catalog_multiselect')->shouldBe(true);
        $this->supports('pim_reference_data_multiselect')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_options_data()
    {
        $changes = ['data' => ['42', '43'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn([
            'data' => ['42', '43'],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }

    function it_get_changes_when_changing_options_data()
    {
        $changes = ['data' => ['42', '43', '45'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['42', '44']];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => ['42', '43', '45'],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }

    function it_returns_null_when_option_is_the_same()
    {
        $changes = ['data' => ['42', '44'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['42', '44'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_compares_in_a_case_insensitive_way(): void
    {
        $changes = ['data' => ['UPPER_CASE', 'lower_CASE'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['LOWER_case', 'upper_case'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_returns_a_change_when_data_is_not_an_array_of_strings(): void
    {
        $changes = ['data' => 'toto', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => 'toto'];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => 'toto',
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);

        $changes = ['data' => ['toto', 42], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['toto', 42]];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => ['toto', 42],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }
}
