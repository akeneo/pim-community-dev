<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;

class MetricComparatorSpec extends ObjectBehavior
{
    function let(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getId()->willReturn(713705);
        $value->getScope()->willReturn('ecommerce');
        $value->getLocale()->willReturn('fr_FR');
        $attribute->getId()->willReturn(1337);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
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
    ){
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
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
                'locale' => 'fr_FR',
            ],
        ]);
    }

    function it_detects_changes_when_changing_metric_unit(
        $value,
        Model\Metric $metric
    ){
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
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
                'locale' => 'fr_FR',
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
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
                'locale' => 'fr_FR',
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
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
                'locale' => 'fr_FR',
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
