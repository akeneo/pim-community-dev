<?php

namespace spec\Pim\Component\Catalog\Comparator\Attribute;

use PhpSpec\ObjectBehavior;

class MetricComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_metric']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\ComparatorInterface');
    }

    function it_supports_metric_type()
    {
        $this->supports('pim_catalog_metric')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_metric()
    {
        $changes   = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_unit()
    {
        $changes   = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['data' => 100, 'unit' => 'GRAM']];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_data()
    {
        $changes   = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['data' => 1, 'unit' => 'KILOGRAM']];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_data_and_unit_are_the_same()
    {
        $changes   = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];

        $this->compare($changes, $originals)->shouldReturn(null);
    }
}
