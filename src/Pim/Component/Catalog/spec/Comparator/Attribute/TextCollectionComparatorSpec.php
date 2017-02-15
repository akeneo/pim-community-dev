<?php

namespace spec\Pim\Component\Catalog\Comparator\Attribute;

use PhpSpec\ObjectBehavior;

class TextCollectionComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_text_collection']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\ComparatorInterface');
    }

    function it_supports_price_type()
    {
        $this->supports('pim_catalog_text_collection')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_text()
    {
        $changes = ['data' => [
            'foo',
            'bar',
        ], 'locale' => null, 'scope' => null];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn([
            'data' => [
                'foo',
                'bar'
            ],
            'locale' => null,
            'scope'  => null,
        ]);
    }

    function it_get_changes_when_order_changes()
    {
        $changes = ['data' => [
            'foo',
            'bar',
            'baz',
        ], 'locale' => null, 'scope' => null];
        $originals = ['data' => [
            'baz',
            'bar',
            'foo',
        ], 'locale' => null, 'scope' => null];

        $this->compare($changes, $originals)->shouldReturn([
            'data' => [
                'foo',
                'bar',
                'baz',
            ],
            'locale' => null,
            'scope'  => null,
        ]);
    }

    function it_get_changes_when_changing_text()
    {
        $changes = ['data' => [
            'foo',
            'bar',
        ], 'locale' => null, 'scope' => null];
        $originals = ['data' => [
            'foobar',
            'baz',
        ], 'locale' => null, 'scope' => null];

        $this->compare($changes, $originals)->shouldReturn([
            'data' => [
                'foo',
                'bar'
            ],
            'locale' => null,
            'scope'  => null,
        ]);
    }

    function it_returns_null_when_text_are_the_same()
    {
        $changes = ['data' => [
            'foo',
            'bar'
        ], 'locale' => null, 'scope' => null];
        $originals = ['data' => [
            'foo',
            'bar'
        ], 'locale' => null, 'scope' => null];

        $this->compare($changes, $originals)->shouldReturn(null);
    }
}
