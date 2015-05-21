<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;

class PricesComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\AttributeComparatorInterface');
    }

    function it_supports_price_type()
    {
        $this->supportsComparison('pim_catalog_price_collection')->shouldBe(true);
        $this->supportsComparison('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_price()
    {
        $changes = ['value' => [
            ['data' => '100', 'currency' => 'EUR'],
            ['data' => '120', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];
        $originals = [];

        $this->getChanges($changes, $originals)->shouldReturn([
            'locale' => null,
            'scope'  => null,
            'value' => [
                ['data' => '100', 'currency' => 'EUR'],
                ['data' => '120', 'currency' => 'USD']
            ],
        ]);
    }

    function it_get_changes_when_changing_price()
    {
        $changes = ['value' => [
            ['data' => '100', 'currency' => 'EUR'],
            ['data' => '120', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];
        $originals = ['value' => [
            ['data' => '90', 'currency' => 'EUR'],
            ['data' => '110', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];

        $this->getChanges($changes, $originals)->shouldReturn([
            'locale' => null,
            'scope'  => null,
            'value' => [
                ['data' => '100', 'currency' => 'EUR'],
                ['data' => '120', 'currency' => 'USD']
            ],
        ]);
    }

    function it_returns_null_when_prices_are_the_same()
    {
        $changes = ['value' => [
            ['data' => '100', 'currency' => 'EUR'],
            ['data' => '120', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];
        $originals = ['value' => [
            ['data' => '100', 'currency' => 'EUR'],
            ['data' => '120', 'currency' => 'USD'],
        ], 'locale' => null, 'scope' => null];

        $this->getChanges($changes, $originals)->shouldReturn(null);
    }
}
