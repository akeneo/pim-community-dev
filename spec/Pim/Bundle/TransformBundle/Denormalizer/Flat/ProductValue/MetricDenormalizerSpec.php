<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

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

    function it_denormalizes_a_existing_metric(ProductValueInterface $metricValue, MetricInterface $metric, AttributeInterface $weight, $metricFactory)
    {
        $context = ['value' => $metricValue];

        $metricValue->getMetric()->willReturn($metric);

        $metric->setUnit('KILOGRAM')->shouldBeCalled();

        $this->denormalize('KILOGRAM', 'className', null, $context)->shouldReturn($metric);
    }

    function it_denormalizes_a_new_metric(ProductValueInterface $metricValue, MetricInterface $metric, AttributeInterface $weight, $metricFactory)
    {
        $context = ['value' => $metricValue];

        $metricValue->getMetric()->willReturn(null);
        $metricValue->getAttribute()->willReturn($weight);
        $weight->getMetricFamily()->willReturn('Weight');

        $metricFactory->createMetric('Weight')->willReturn($metric);

        $metric->setData('100')->shouldBeCalled();

        $this->denormalize('100', 'className', null, $context)->shouldReturn($metric);
    }

    function it_returns_null_if_the_data_is_empty(ProductValueInterface $productValueInterface)
    {
        $this->denormalize('', 'className', null, [])->shouldReturn(null);
        $this->denormalize(null, 'className', null, [])->shouldReturn(null);
    }
}
