<?php

namespace spec\Pim\Bundle\TransformBundle\Converter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class MetricConverterSpec extends ObjectBehavior
{
    function let(MeasureConverter $converter)
    {
        $this->beConstructedWith($converter);
    }

    function it_converts_metric_values_given_the_configured_base_unit_in_the_channel(
        $converter,
        ProductValueInterface $weightValue,
        ProductValueInterface $surfaceValue,
        ProductValueInterface $nameValue,
        AttributeInterface $weight,
        AttributeInterface $surface,
        AttributeInterface $name,
        MetricInterface $weightMetric,
        MetricInterface $surfaceMetric,
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

        $product->getValues()->willReturn(array($weightValue, $surfaceValue, $nameValue));

        $channel->getConversionUnits()->willReturn(array('weight' => 'GRAM'));

        $converter->setFamily('Weight')->shouldBeCalled();
        $converter->convert('KILOGRAM', 'GRAM', 1)->willReturn(0.001);

        $converter->setFamily('Surface')->shouldNotBeCalled();

        $weightMetric->setData(0.001)->shouldBeCalled();
        $weightMetric->setUnit('GRAM')->shouldBeCalled();

        $this->convert($product, $channel);
    }

    function it_does_not_convert_null_metric_values_in_the_channel(
        $converter,
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

        $product->getValues()->willReturn(array($weightValue));

        $channel->getConversionUnits()->willReturn(array('weight' => 'GRAM'));

        $converter->setFamily('Weight')->shouldNotBeCalled();
        $converter->convert('KILOGRAM', 'GRAM', 1)->shouldNotBeCalled();
        $weightMetric->setData(null)->shouldNotBeCalled();
        $weightMetric->setUnit('GRAM')->shouldNotBeCalled();

        $this->convert($product, $channel);
    }
}
