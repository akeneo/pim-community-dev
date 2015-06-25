<?php

namespace spec\Pim\Component\Catalog\Comparator\Attribute;

use PhpSpec\ObjectBehavior;

class MetricComparatorSpec extends ObjectBehavior
{
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
        $changes   = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_unit()
    {
        $changes   = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['value' => ['data' => 100, 'unit' => 'GRAM']];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_data()
    {
        $changes   = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['value' => ['data' => 1, 'unit' => 'KILOGRAM']];

        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_data_and_unit_are_the_same()
    {
        $changes   = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];

        $this->compare($changes, $originals)->shouldReturn(null);
    }
}
