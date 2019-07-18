<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\MetricNormalizer as StandardMetricNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\MetricLocalizer;


class MetricNormalizerSpec extends ObjectBehavior
{
    function let(StandardMetricNormalizer $metricNormalizer, MetricLocalizer $metricLocalizer)
    {
        $this->beConstructedWith($metricNormalizer, $metricLocalizer);
    }

    function it_normalizes_a_metric_product_value(StandardMetricNormalizer $metricNormalizer, MetricLocalizer $metricLocalizer, MetricValue $value)
    {
        $metricNormalizer->normalize($value, 'standard', ['locale' => 'en_US'])->willReturn(
            [
                'amount' => new Metric('weight', 'KILOGRAM', 10, 'GRAM', 10)
            ]
        );

        $metric = [
            'amount' => 10,
            'unit' => 'KILOGRAM'
        ];

        $metricLocalizer->localize($metric, ['locale' => 'en_US'])->willReturn($metric);

        $value->getAmount()->willReturn(10);
        $value->getUnit()->willReturn('KILOGRAM');
        $this->normalize($value, 'en_US')->shouldReturn('10 KILOGRAM');
    }

    function it_supports_only_metric_attributes()
    {
        $this->supports(AttributeTypes::METRIC)->shouldReturn(true);
        $this->supports(AttributeTypes::TEXT)->shouldReturn(false);
    }
}
