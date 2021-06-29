<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\MetricLocalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\MetricNormalizer as StandardMetricNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\TranslatorInterface;

class MetricNormalizerSpec extends ObjectBehavior
{
    function let(
        StandardMetricNormalizer $metricNormalizer,
        MetricLocalizer $metricLocalizer,
        GetUnitTranslations $getUnitTranslations
    ) {
        $this->beConstructedWith($metricNormalizer, $metricLocalizer, $getUnitTranslations);
    }

    function it_normalizes_a_metric_product_value(
        StandardMetricNormalizer $metricNormalizer,
        MetricLocalizer $metricLocalizer,
        GetUnitTranslations $getUnitTranslations,
        MetricValue $value
    ) {
        $metricNormalizer->normalize($value, 'standard', ['locale' => 'en_US'])->willReturn(
            [
                'amount' => new Metric('weight', 'KILOGRAM', 10, 'GRAM', 10),
            ]
        );

        $metric = [
            'amount' => 10,
            'unit' => 'KILOGRAM',
        ];

        $metricLocalizer->localize($metric, ['locale' => 'en_US'])->willReturn($metric);

        $getUnitTranslations->byMeasurementFamilyCodeAndLocale('weight', 'en_US')->willReturn(['KILOGRAM' => 'Kilogram']);

        $value->getAmount()->willReturn(10);
        $value->getUnit()->willReturn('KILOGRAM');
        $this->normalize($value, 'en_US')->shouldReturn('10 Kilogram');
    }

    function it_only_supports_metric_attributes()
    {
        $this->supports(AttributeTypes::METRIC)->shouldReturn(true);
        $this->supports(AttributeTypes::TEXT)->shouldReturn(false);
    }
}
