<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;

class MetricComparatorSpec extends ObjectBehavior
{
    function let(
        Model\ProductValueInterface $value,
        Model\AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_supports_metric_type(
        $value,
        $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');

        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_detects_changes_when_changing_metric_data(
        $value,
        Model\Metric $metric
    ) {
        $submittedData = [
            'metric' => [
                'data' => '100',
                'unit' => 'KILOGRAM',
                'family' => 'Weight',
            ],
        ];

        $value->getMetric()->willReturn($metric);
        $metric->getData()->willReturn(80);
        $metric->getUnit()->willReturn('KILOGRAM');

        $this->getChanges($value, $submittedData)->shouldReturn([
            'metric' => [
                'data' => '100',
                'unit' => 'KILOGRAM',
            ],
        ]);
    }

    function it_detects_changes_when_changing_metric_unit(
        $value,
        Model\Metric $metric
    ) {
        $submittedData = [
            'metric' => [
                'data' => '100',
                'unit' => 'GRAM',
                'family' => 'Weight',
            ],
        ];

        $value->getMetric()->willReturn($metric);
        $metric->getData()->willReturn(100);
        $metric->getUnit()->willReturn('KILOGRAM');

        $this->getChanges($value, $submittedData)->shouldReturn([
            'metric' => [
                'data' => '100',
                'unit' => 'GRAM',
            ],
        ]);
    }

    function it_detects_no_changes_when_data_is_empty_even_if_the_unit_has_changed(
        $value,
        Model\Metric $metric
    ) {
        $submittedData = [
            'metric' => [
                'data' => '',
                'unit' => 'KILOGRAM',
                'family' => 'Weight',
            ],
        ];

        $value->getMetric()->willReturn($metric);
        $metric->getData()->willReturn(null);
        $metric->getUnit()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_changes_when_setting_empty_value_on_metric_that_used_to_have_a_data(
        $value,
        Model\Metric $metric
    ) {
        $submittedData = [
            'metric' => [
                'data' => '',
                'unit' => 'KILOGRAM',
                'family' => 'Weight',
            ],
        ];

        $value->getMetric()->willReturn($metric);
        $metric->getData()->willReturn(100);
        $metric->getUnit()->willReturn('KILOGRAM');

        $this->getChanges($value, $submittedData)->shouldReturn([
            'metric' => [
                'data' => '',
                'unit' => 'KILOGRAM',
            ],
        ]);
    }

    function it_detects_no_changes_when_data_and_unit_are_the_same(
        $value,
        Model\Metric $metric
    ) {
        $submittedData = [
            'metric' => [
                'data' => '100',
                'unit' => 'KILOGRAM',
                'family' => 'Weight',
            ],
        ];

        $value->getMetric()->willReturn($metric);
        $metric->getData()->willReturn(100);
        $metric->getUnit()->willReturn('KILOGRAM');

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_always_detect_changes_when_there_is_no_current_metric(
        $value
    ) {
        $submittedData = [
            'metric' => [
                'data' => '100',
                'unit' => 'KILOGRAM',
                'family' => 'Weight',
            ],
        ];

        $value->getMetric()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'metric' => [
                'data' => '100',
                'unit' => 'KILOGRAM',
            ],
        ]);
    }

    function it_detects_no_changes_when_the_new_metric_data_is_not_available(
        $value,
        Model\Metric $metric
    ) {
        $submittedData = [
            'metric' => [],
        ];

        $value->getMetric()->willReturn($metric);
        $metric->getData()->willReturn(100);
        $metric->getUnit()->willReturn('KILOGRAM');

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_changes_when_the_old_and_new_metric_data_are_not_available(
        $value,
        Model\Metric $metric
    ) {
        $submittedData = [
            'metric' => [
                'data' => '',
                'unit' => 'KILOGRAM',
            ],
        ];

        $value->getMetric()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
