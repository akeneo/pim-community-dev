<?php

namespace spec\Pim\Component\Localization\Normalizer\Structured;

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
        $this->supportsNormalization($metric, 'xml')->shouldReturn(true);
        $this->supportsNormalization($metric, 'json')->shouldReturn(true);
        $this->supportsNormalization($metric, 'csv')->shouldReturn(false);
        $this->supportsNormalization($metric, 'flat')->shouldReturn(false);
    }

    function it_normalizes_metric_with_decimal_multiple_format($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ',', 'field_name' => 'metric', 'metric_format' => 'multiple_fields'];
        $metric->getData()->willReturn(25.3);

        $data = ['data' => '25.30', 'unit' => 'GRAM'];
        $metricNormalizer->normalize($metric, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn('25,30');
        $this->normalize($metric, null, $options)->shouldReturn(['data' => '25,30', 'unit' => 'GRAM']);

        $options['decimal_separator'] = '.';
        $metricNormalizer->normalize($metric, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn('25.30');
        $this->normalize($metric, null, $options)->shouldReturn(['data' => '25.30', 'unit' => 'GRAM']);
    }

    function it_normalizes_metric_without_decimal($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ','];
        $metric->getData()->willReturn(25);

        $data = ['data' => '25', 'unit' => 'GRAM'];
        $metricNormalizer->normalize($metric, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn('25');
        $this->normalize($metric, null, $options)->shouldReturn(['data' => '25', 'unit' => 'GRAM']);
    }

    function it_normalizes_metric_without_decimal_as_string($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ','];
        $metric->getData()->willReturn('25');

        $data = ['data' => '25', 'unit' => 'GRAM'];
        $metricNormalizer->normalize($metric, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn('25');
        $this->normalize($metric, null, $options)->shouldReturn(['data' => '25', 'unit' => 'GRAM']);
    }

    function it_normalizes_null_metric($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ','];
        $metric->getData()->willReturn(null);

        $data = ['data' => '', 'unit' => ''];
        $metricNormalizer->normalize($metric, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn('');
        $this->normalize($metric, null, $options)->shouldReturn(['data' => '', 'unit' => '']);
    }

    function it_normalizes_empty_metric($metricNormalizer, $localizer, MetricInterface $metric)
    {
        $options = ['decimal_separator' => ','];
        $metric->getData()->willReturn('');

        $data = ['data' => '', 'unit' => ''];
        $metricNormalizer->normalize($metric, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn('');
        $this->normalize($metric, null, $options)->shouldReturn(['data' => '', 'unit' => '']);
    }
}
