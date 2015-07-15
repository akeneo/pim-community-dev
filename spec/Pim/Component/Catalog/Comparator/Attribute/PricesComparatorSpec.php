<?php

namespace spec\Pim\Component\Catalog\Comparator\Attribute;

use PhpSpec\ObjectBehavior;

class PricesComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_price_collection']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\ComparatorInterface');
    }

    function it_supports_price_type()
    {
        $this->supports('pim_catalog_price_collection')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_price()
    {
        $changes = ['data' => [
            ['data' => '100', 'currency' => 'EUR'],
            ['data' => '120', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn([
            'data' => [
                ['data' => '100', 'currency' => 'EUR'],
                ['data' => '120', 'currency' => 'USD']
            ],
            'locale' => null,
            'scope'  => null,
        ]);
    }

    function it_get_changes_when_changing_price()
    {
        $changes = ['data' => [
            ['data' => '100', 'currency' => 'EUR'],
            ['data' => '120', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];
        $originals = ['data' => [
            ['data' => '90', 'currency' => 'EUR'],
            ['data' => '110', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];

        $this->compare($changes, $originals)->shouldReturn([
            'data' => [
                ['data' => '100', 'currency' => 'EUR'],
                ['data' => '120', 'currency' => 'USD']
            ],
            'locale' => null,
            'scope'  => null,
        ]);
    }

    function it_returns_null_when_prices_are_the_same()
    {
        $changes = ['data' => [
            ['data' => '100', 'currency' => 'EUR'],
            ['data' => '120', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];
        $originals = ['data' => [
            ['data' => '100', 'currency' => 'EUR'],
            ['data' => '120', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_returns_null_when_prices_are_the_same_but_with_different_format()
    {
        $changes = ['data' => [
            ['data' => '100', 'currency' => 'EUR'],
            ['data' => '120.50', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];
        $originals = ['data' => [
            ['data' => '100.00', 'currency' => 'EUR'],
            ['data' => '120.5000', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];

        $this->compare($changes, $originals)->shouldReturn(null);
    }
}
