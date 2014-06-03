<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\Metric;
class MetricComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_supports_metric_type(
        AbstractProductValue $value,
        AbstractAttribute $attribute
    )
    {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');

        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_detects_changes_when_changing_metric_data(
        AbstractProductValue $value,
        Metric $metric
    ){
        $submittedData = [
            'id' => '1',
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
            'id' => '1',
            'metric' => [
                'data' => '100',
                'unit' => 'KILOGRAM',
            ],
        ]);
    }

    function it_detects_changes_when_changing_metric_unit(
        AbstractProductValue $value,
        Metric $metric
    ){
        $submittedData = [
            'id' => '1',
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
            'id' => '1',
            'metric' => [
                'data' => '100',
                'unit' => 'GRAM',
            ],
        ]);
    }

    function it_detects_no_changes_when_data_is_empty_even_if_the_unit_has_changed(
        AbstractProductValue $value,
        Metric $metric
    ) {
        $submittedData = [
            'id' => '1',
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
        AbstractProductValue $value,
        Metric $metric
    ) {
        $submittedData = [
            'id' => '1',
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
            'id' => '1',
            'metric' => [
                'data' => '',
                'unit' => 'KILOGRAM',
            ],
        ]);
    }

    function it_detects_no_changes_when_data_and_unit_are_the_same(
        AbstractProductValue $value,
        Metric $metric
    ) {
        $submittedData = [
            'id' => '1',
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
        AbstractProductValue $value
    ) {
        $submittedData = [
            'id' => '1',
            'metric' => [
                'data' => '100',
                'unit' => 'KILOGRAM',
                'family' => 'Weight',
            ],
        ];

        $value->getMetric()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'id' => '1',
            'metric' => [
                'data' => '100',
                'unit' => 'KILOGRAM',
            ],
        ]);
    }

    function it_detects_no_changes_when_the_new_metric_data_is_not_available(
        AbstractProductValue $value,
        Metric $metric
    ) {
        $submittedData = [
            'id' => '1',
            'metric' => [],
        ];

        $value->getMetric()->willReturn($metric);
        $metric->getData()->willReturn(100);
        $metric->getUnit()->willReturn('KILOGRAM');

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_changes_when_the_old_and_new_metric_data_are_not_available(
        AbstractProductValue $value,
        Metric $metric
    ) {
        $submittedData = [
            'id' => '1',
            'metric' => [
                'data' => '',
                'unit' => 'KILOGRAM',
            ],
        ];

        $value->getMetric()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
