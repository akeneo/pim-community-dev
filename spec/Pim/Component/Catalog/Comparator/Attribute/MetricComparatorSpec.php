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

    function it_get_changes_when_adding_metric_as_integer()
    {
        $data      = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = [];
        $changes   = ['data' => ['data' => 100.00, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_adding_metric_as_string()
    {
        $data      = ['data' => ['data' => '100', 'unit' => 'KILOGRAM']];
        $originals = [];
        $changes   = ['data' => ['data' => 100.00, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_adding_metric_as_float()
    {
        $data      = ['data' => ['data' => 100.00, 'unit' => 'KILOGRAM']];
        $originals = [];
        $changes   = ['data' => ['data' => 100.00, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_unit()
    {
        $data      = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $changes   = ['data' => ['data' => 100.00, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['data' => 100, 'unit' => 'GRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_data()
    {
        $data      = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $changes   = ['data' => ['data' => 100.00, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['data' => 1, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_data_and_unit_are_the_same_with_string()
    {
        $data      = ['data' => ['data' => '100', 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn(null);
    }

    function it_returns_null_when_data_and_unit_are_the_same_with_integer()
    {
        $data      = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn(null);
    }

    function it_returns_null_when_data_and_unit_are_the_same_with_float()
    {
        $data      = ['data' => ['data' => 100.00, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['data' => 100, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn(null);
    }

}
