<?php

namespace spec\Pim\Component\Catalog\Converter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\MetricFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;

class MetricConverterSpec extends ObjectBehavior
{
    function let(MeasureConverter $converter, MetricFactory $factory)
    {
        $this->beConstructedWith($converter, $factory);
    }

    function it_converts_metric_values_given_the_configured_base_unit_in_the_channel(
        $converter,
        $factory,
        ProductValueInterface $weightValue,
        ProductValueInterface $surfaceValue,
        ProductValueInterface $nameValue,
        AttributeInterface $weight,
        AttributeInterface $surface,
        AttributeInterface $name,
        MetricInterface $weightMetric,
        MetricInterface $surfaceMetric,
        MetricInterface $convertedMetric,
        ProductInterface $product,
        ChannelInterface $channel
    ) {
        $weightValue->getAttribute()->willReturn($weight);
        $weightValue->getData()->willReturn($weightMetric);
        $weight->getCode()->willReturn('weight');

        $weightMetric->getFamily()->willReturn('Weight');
        $weightMetric->getUnit()->willReturn('KILOGRAM');
        $weightMetric->getData()->willReturn(1);

        $surfaceValue->getAttribute()->willReturn($surface);
        $surfaceValue->getData()->willReturn($surfaceMetric);
        $surface->getCode()->willReturn('surface');

        $surfaceMetric->getFamily()->willReturn('Surface');
        $surfaceMetric->getUnit()->willReturn('METER_SQUARE');
        $surfaceMetric->getData()->willReturn(10);

        $nameValue->getAttribute()->willReturn($name);
        $nameValue->getData()->willReturn('foobar');

        $product->getValues()->willReturn([$weightValue, $surfaceValue, $nameValue]);

        $channel->getConversionUnits()->willReturn(['weight' => 'GRAM']);

        $converter->setFamily('Weight')->shouldBeCalled()->willReturn($converter);
        $converter->convert('KILOGRAM', 'GRAM', 1)->willReturn(0.001);

        $converter->setFamily('Surface')->shouldNotBeCalled();

        $factory->createMetric('Weight', 'GRAM', 0.001)->shouldBeCalled()->willReturn($convertedMetric);
        $weightValue->setMetric($convertedMetric)->shouldBeCalled();

        $this->convert($product, $channel);
    }

    function it_does_not_convert_null_metric_values_in_the_channel(
        $converter,
        $factory,
        ProductValueInterface $weightValue,
        AttributeInterface $weight,
        MetricInterface $weightMetric,
        ProductInterface $product,
        ChannelInterface $channel
    ) {
        $weightValue->getAttribute()->willReturn($weight);
        $weightValue->getData()->willReturn($weightMetric);
        $weight->getCode()->willReturn('weight');

        $weightMetric->getFamily()->willReturn('Weight');
        $weightMetric->getUnit()->willReturn(null);
        $weightMetric->getData()->willReturn(null);

        $product->getValues()->willReturn([$weightValue]);

        $channel->getConversionUnits()->willReturn(['weight' => 'GRAM']);

        $converter->setFamily('Weight')->shouldNotBeCalled();
        $converter->convert('KILOGRAM', 'GRAM', 1)->shouldNotBeCalled();
        $factory->createMetric(Argument::cetera())->shouldNotBeCalled();
        $weightValue->setMetric(Argument::cetera())->shouldNotBeCalled();

        $this->convert($product, $channel);
    }
}
