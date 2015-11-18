<?php

namespace spec\Pim\Component\Localization\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetricNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $metricNormalizer, LocalizerInterface $localizer)
    {
        $this->beConstructedWith($metricNormalizer, $localizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_type(MetricInterface $metric)
    {
        $this->supportsNormalization($metric, 'csv')->shouldReturn(true);
        $this->supportsNormalization($metric, 'flat')->shouldReturn(true);
        $this->supportsNormalization($metric, 'versioning')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'versioning')->shouldReturn(false);
    }

    function it_normalizes_metric_with_decimal_multiple_format($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric', 'metric_format' => 'multiple_fields'];
        $metric->getData()->willReturn(25.3);
        $localizer->localize(['data' => '25.30'], $options)->willReturn(['data' => '25,30']);

        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '25.30', 'unit' => 'GRAM']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '25,30', 'unit' => 'GRAM']);

        $options['decimal_separator'] ='.';;
        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '25.30', 'unit' => 'GRAM']);
        $localizer->localize(['data' => '25.30'], $options)->willReturn(['data' => '25.30']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '25.30', 'unit' => 'GRAM']);
    }

    function it_normalizes_metric_with_decimal_simple_format($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric'];
        $metric->getData()->willReturn(25.3);
        $localizer->localize(['data' => '25.30 GRAM'], $options)->willReturn(['data' => '25,30 GRAM']);
        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '25.30 GRAM']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '25,30 GRAM']);

        $options['decimal_separator'] = '.';
        $localizer->localize(['data' => '25.30 GRAM'], $options)->willReturn(['data' => '25.30 GRAM']);
        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '25.30 GRAM']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '25.30 GRAM']);
    }

    function it_normalizes_metric_without_decimal_multiple_format($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric', 'metric_format' => 'multiple_fields'];
        $metric->getData()->willReturn(25);
        $localizer->localize(['data' => '25'], $options)->willReturn(['data' => '25']);

        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '25', 'unit' => 'GRAM']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '25', 'unit' => 'GRAM']);
    }

    function it_normalizes_metric_without_decimal_simple_format($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric'];
        $metric->getData()->willReturn(25);
        $localizer->localize(['data' => '25 GRAM'], $options)->willReturn(['data' => '25 GRAM']);

        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '25 GRAM']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '25 GRAM']);
    }

    function it_normalizes_metric_without_decimal_as_string_multiple_format(
        $metricNormalizer,
        $localizer,
        MetricInterface $metric
    ) {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric', 'metric_format' => 'multiple_fields'];
        $metric->getData()->willReturn('25');
        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '25', 'unit' => 'GRAM']);

        $localizer->localize(['data' => '25'], $options)->willReturn(['data' => '25']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '25', 'unit' => 'GRAM']);
    }

    function it_normalizes_metric_without_decimal_as_string_simple_format(
        $metricNormalizer,
        $localizer,
        MetricInterface $metric
    ) {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric'];
        $metric->getData()->willReturn('25');
        $localizer->localize(['data' => '25 GRAM'], $options)->willReturn(['data' => '25 GRAM']);

        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '25 GRAM']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '25 GRAM']);
    }

    function it_normalizes_null_metric_multiple_format($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric', 'metric_format' => 'multiple_fields'];
        $metric->getData()->willReturn(null);
        $localizer->localize(['data' => ''], $options)->willReturn(['data' => '']);

        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '', 'unit' => '']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '', 'unit' => '']);
    }

    function it_normalizes_null_metric_simple_format($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric'];
        $metric->getData()->willReturn(null);
        $localizer->localize(['data' => ''], $options)->willReturn(['data' => '']);

        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '']);
    }

    function it_normalizes_empty_metric_multiple_format($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric', 'metric_format' => 'multiple_fields'];
        $metric->getData()->willReturn('');
        $localizer->localize(['data' => ''], $options)->willReturn(['data' => '']);

        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '', 'unit' => '']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '', 'unit' => '']);
    }

    function it_normalizes_empty_metric_simple_format($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric'];
        $metric->getData()->willReturn('');
        $localizer->localize(['data' => ''], $options)->willReturn(['data' => '']);

        $metricNormalizer->normalize($metric, null, $options)->willReturn(['metric' => '']);
        $this->normalize($metric, null, $options)->shouldReturn(['metric' => '']);
    }
}
