<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;

class MetricDenormalizerSpec extends ObjectBehavior
{
    function let(MetricFactory $factory)
    {
        $this->beConstructedWith(['pim_catalog_metric'], $factory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\MetricDenormalizer');
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

    function it_denormalizes_data_into_metric(AttributeInterface $attribute, $factory, MetricInterface $metric)
    {
        $attribute->getMetricFamily()->willReturn('Frequency');

        $factory
            ->createMetric('Frequency')
            ->shouldBeCalled()
            ->willReturn($metric);

        $metric->setData(3)->shouldBeCalled();
        $metric->setUnit('GIGAHERTZ')->shouldBeCalled();

        $this
            ->denormalize(
                [
                    'data' => 3,
                    'unit' => 'GIGAHERTZ'
                ],
                'pim_catalog_metric',
                'json',
                ['attribute' => $attribute]
            )
            ->shouldReturn($metric);
    }
}
