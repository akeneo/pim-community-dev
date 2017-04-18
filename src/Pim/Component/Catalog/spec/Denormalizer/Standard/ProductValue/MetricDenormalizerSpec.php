<?php

namespace spec\Pim\Component\Catalog\Denormalizer\Standard\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\MetricFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\MetricInterface;

class MetricDenormalizerSpec extends ObjectBehavior
{
    function let(MetricFactory $factory)
    {
        $this->beConstructedWith(['pim_catalog_metric'], $factory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Denormalizer\Standard\ProductValue\MetricDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_metric_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_metric', 'standard')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'standard')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_metric', 'csv')->shouldReturn(false);
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_metric', 'standard')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_metric', 'standard')->shouldReturn(null);
        $this->denormalize([], 'pim_catalog_metric', 'standard')->shouldReturn(null);
    }

    function it_denormalizes_data_into_metric(
        $factory,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $attribute->getMetricFamily()->willReturn('Frequency');

        $factory
            ->createMetric('Frequency', 'GIGAHERTZ', 3.5)
            ->shouldBeCalled()
            ->willReturn($metric);

        $context = ['attribute' => $attribute];

        $this
            ->denormalize(
                [
                    'amount' => 3.5,
                    'unit' => 'GIGAHERTZ'
                ],
                'pim_catalog_metric',
                'standard',
                $context
            )
            ->shouldReturn($metric);
    }
}
