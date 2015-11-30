<?php

namespace spec\Pim\Component\Localization\Denormalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MetricDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $metricDenormalizer, LocalizerInterface $localizer)
    {
        $this->beConstructedWith($metricDenormalizer, $localizer, ['pim_catalog_metric']);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_metric_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_metric', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_metric', 'csv')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_metric', 'json')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_metric', 'json')->shouldReturn(null);
        $this->denormalize([], 'pim_catalog_metric', 'json')->shouldReturn(null);
    }

    function it_denormalizes_data_into_metric_with_english_format(
        $metricDenormalizer,
        $localizer,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $attribute->getMetricFamily()->willReturn('Frequency');
        $options = ['attribute' => $attribute, 'locale' => 'en_US'];
        $data    = ['data' => 3.85, 'unit' => 'GIGAHERTZ'];

        $metricDenormalizer->denormalize($data, 'pim_catalog_metric', 'json', $options)->willReturn($metric);

        $metric->getData()->willReturn(3.85);
        $localizer->localize(3.85, $options)->willReturn(3.85);
        $metric->setData(3.85)->shouldBeCalled();

        $this->denormalize($data, 'pim_catalog_metric', 'json', $options)->shouldReturn($metric);
    }

    function it_denormalizes_data_into_metric_with_french_format(
        $metricDenormalizer,
        $localizer,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $attribute->getMetricFamily()->willReturn('Frequency');
        $options = ['attribute' => $attribute, 'locale' => 'fr_FR'];
        $data    = ['data' => 3.85, 'unit' => 'GIGAHERTZ'];

        $metricDenormalizer->denormalize($data, 'pim_catalog_metric', 'json', $options)->willReturn($metric);

        $metric->getData()->willReturn(3.85);
        $localizer->localize(3.85, $options)->willReturn('3,85');
        $metric->setData('3,85')->shouldBeCalled();

        $this->denormalize($data, 'pim_catalog_metric', 'json', $options)->shouldReturn($metric);
    }
}
