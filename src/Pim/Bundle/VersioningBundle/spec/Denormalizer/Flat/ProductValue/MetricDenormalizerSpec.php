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

    function it_denormalizes_a_existing_metric_from_many_fields_and_set_only_the_unit(
        ProductValueInterface $metricValue,
        MetricInterface $metric
    ) {
        $context = ['value' => $metricValue];

        $metricValue->getMetric()->willReturn($metric);

        $metric->setUnit('KILOGRAM')->shouldBeCalled();

        $this->denormalize('KILOGRAM', 'className', null, $context)->shouldReturn($metric);
    }

    function it_denormalizes_a_new_metric_from_many_fields_and_set_only_the_data(
        ProductValueInterface $metricValue,
        MetricInterface $metric,
        AttributeInterface $weight,
        $metricFactory
    ) {
        $context = ['value' => $metricValue];

        $metricValue->getMetric()->willReturn(null);
        $metricValue->getAttribute()->willReturn($weight);
        $weight->getMetricFamily()->willReturn('Weight');

        $metricFactory->createMetric('Weight')->willReturn($metric);

        $metric->setData('100')->shouldBeCalled();

        $this->denormalize('100', 'className', null, $context)->shouldReturn($metric);
    }

    function it_denormalizes_a_new_metric_from_a_single_field_and_set_data_and_unit(
        ProductValueInterface $metricValue,
        MetricInterface $metric,
        AttributeInterface $weight,
        $metricFactory
    ) {
        $context = ['value' => $metricValue];

        $metricValue->getMetric()->willReturn(null);
        $metricValue->getAttribute()->willReturn($weight);
        $weight->getMetricFamily()->willReturn('Weight');

        $metricFactory->createMetric('Weight')->willReturn($metric);

        $metric->setData('100')->shouldBeCalled();
        $metric->setUnit('KILOGRAM')->shouldBeCalled();

        $this->denormalize('100 KILOGRAM', 'className', null, $context)->shouldReturn($metric);
    }

    function it_returns_a_metric_if_the_data_is_empty(ProductValueInterface $metricValue, MetricInterface $metric)
    {
        $metricValue->getMetric()->willReturn($metric);

        $this->denormalize('', 'className', null, ['value' => $metricValue])->shouldReturn($metric);
        $this->denormalize(null, 'className', null, ['value' => $metricValue])->shouldReturn($metric);
    }
}
