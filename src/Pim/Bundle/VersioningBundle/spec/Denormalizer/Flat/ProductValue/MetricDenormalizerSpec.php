<?php

namespace spec\Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\MetricFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class MetricDenormalizerSpec extends ObjectBehavior
{
    function let(MetricFactory $metricFactory)
    {
        $this->beConstructedWith(
            ['pim_catalog_metric'],
            $metricFactory
        );
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_denormalizes_a_new_metric(
        $metricFactory,
        ProductValueInterface $metricValue,
        MetricInterface $metric,
        AttributeInterface $weight
    ) {
        $metricValue->getAttribute()->willReturn($weight);
        $weight->getMetricFamily()->willReturn('Weight');

        $metricFactory->createMetric('Weight', 'KILOGRAM', 100)->willReturn($metric);

        $this->denormalize('100 KILOGRAM', 'className', null, ['value' => $metricValue])->shouldReturn($metric);
    }

    function it_returns_a_metric_if_the_data_is_empty(
        $metricFactory,
        ProductValueInterface $metricValue,
        MetricInterface $metric,
        AttributeInterface $weight
    ) {
        $metricValue->getAttribute()->willReturn($weight);
        $weight->getMetricFamily()->willReturn('Weight');

        $metricFactory->createMetric('Weight', null, null)->willReturn($metric);

        $this->denormalize('', 'className', null, ['value' => $metricValue])->shouldReturn($metric);
        $this->denormalize(null, 'className', null, ['value' => $metricValue])->shouldReturn($metric);
    }
}
