<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;

class MetricComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_supports_metric_type()
    {
        $this->supportsComparison('pim_catalog_metric')->shouldBe(true);
        $this->supportsComparison('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_metric()
    {
        $changes   = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = [];

        $this->getChanges($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_unit()
    {
        $changes   = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['value' => ['data' => 100, 'unit' => 'GRAM']];

        $this->getChanges($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_metric_data()
    {
        $changes   = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['value' => ['data' => 1, 'unit' => 'KILOGRAM']];

        $this->getChanges($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_data_and_unit_are_the_same()
    {
        $changes   = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];
        $originals = ['value' => ['data' => 100, 'unit' => 'KILOGRAM']];

        $this->getChanges($changes, $originals)->shouldReturn(null);
    }
}
