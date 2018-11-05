<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use PhpSpec\ObjectBehavior;

class MetricComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_metric']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf(ComparatorInterface::class);
    }

    function it_supports_metric_type()
    {
        $this->supports('pim_catalog_metric')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_metric_as_integer()
    {
        $data = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];
        $originals = [];
        $changes = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_adding_metric_as_string()
    {
        $data = ['data' => ['amount' => '100', 'unit' => 'KILOGRAM']];
        $originals = [];
        $changes = ['data' => ['amount' => '100', 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_adding_metric_as_float()
    {
        $data = ['data' => ['amount' => 100.00, 'unit' => 'KILOGRAM']];
        $originals = [];
        $changes = ['data' => ['amount' => 100.00, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_unit()
    {
        $data = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];
        $changes = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['amount' => 100, 'unit' => 'GRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_data()
    {
        $data = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];
        $changes = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['amount' => 1, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_data_and_unit_are_the_same_with_string()
    {
        $data = ['data' => ['amount' => '100', 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn(null);
    }

    function it_returns_null_when_data_and_unit_are_the_same_with_integer()
    {
        $data = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn(null);
    }

    function it_returns_null_when_data_and_unit_are_the_same_with_float()
    {
        $data = ['data' => ['amount' => 100.00, 'unit' => 'KILOGRAM']];
        $originals = ['data' => ['amount' => 100, 'unit' => 'KILOGRAM']];

        $this->compare($data, $originals)->shouldReturn(null);
    }
}
