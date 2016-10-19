<?php

namespace spec\Pim\Component\Catalog\Denormalizer\Structured\ProductValue;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\MetricFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\MetricInterface;

class MetricDenormalizerSpec extends ObjectBehavior
{
    function let(MetricFactory $factory, LocalizerInterface $localizer)
    {
        $this->beConstructedWith(['pim_catalog_metric'], $factory, $localizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Denormalizer\Structured\ProductValue\MetricDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_metric_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_metric', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_metric', 'csv')->shouldReturn(false);
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_metric', 'json')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_metric', 'json')->shouldReturn(null);
        $this->denormalize([], 'pim_catalog_metric', 'json')->shouldReturn(null);
    }

    function it_denormalizes_data_into_metric_with_en_US_locale(
        $localizer,
        $factory,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $attribute->getMetricFamily()->willReturn('Frequency');

        $factory
            ->createMetric('Frequency')
            ->shouldBeCalled()
            ->willReturn($metric);

        $context = ['attribute' => $attribute, 'locale' => 'en_US'];
        $localizer->localize(3.5, $context)->willReturn(3.5);

        $metric->setData(3.5)->shouldBeCalled();
        $metric->setUnit('GIGAHERTZ')->shouldBeCalled();

        $this
            ->denormalize(
                [
                    'amount' => 3.5,
                    'unit' => 'GIGAHERTZ'
                ],
                'pim_catalog_metric',
                'json',
                $context
            )
            ->shouldReturn($metric);
    }

    function it_denormalizes_data_into_metric_with_fr_FR_locale(
        $localizer,
        $factory,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $attribute->getMetricFamily()->willReturn('Frequency');

        $factory
            ->createMetric('Frequency')
            ->shouldBeCalled()
            ->willReturn($metric);

        $context = ['attribute' => $attribute, 'locale' => 'fr_FR'];
        $localizer->localize(3.5, $context)->willReturn('3,5');

        $metric->setData('3,5')->shouldBeCalled();
        $metric->setUnit('GIGAHERTZ')->shouldBeCalled();

        $this
            ->denormalize(
                [
                    'amount' => 3.5,
                    'unit' => 'GIGAHERTZ'
                ],
                'pim_catalog_metric',
                'json',
                $context
            )
            ->shouldReturn($metric);
    }
}
