<?php

namespace spec\Pim\Component\Catalog\Converter;

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Prophecy\Argument;

class MetricConverterSpec extends ObjectBehavior
{
    function let(MeasureConverter $converter, EntityWithValuesBuilderInterface $productBuilder)
    {
        $this->beConstructedWith($converter, $productBuilder);
    }

    function it_converts_metric_values_given_the_configured_base_unit_in_the_channel(
        $converter,
        $productBuilder,
        ValueInterface $weightValue,
        ValueInterface $surfaceValue,
        ValueInterface $nameValue,
        AttributeInterface $weight,
        AttributeInterface $surface,
        AttributeInterface $name,
        MetricInterface $weightMetric,
        MetricInterface $surfaceMetric,
        ProductInterface $product,
        ChannelInterface $channel
    ) {
        $channel->getConversionUnits()->willReturn(['weight' => 'GRAM']);

        $weightValue->getAttribute()->willReturn($weight);
        $weightValue->getData()->willReturn($weightMetric);
        $weightValue->getLocale()->willReturn(null);
        $weightValue->getScope()->willReturn(null);
        $weight->getCode()->willReturn('weight');

        $weightMetric->getFamily()->willReturn('Weight');
        $weightMetric->getUnit()->willReturn('KILOGRAM');
        $weightMetric->getData()->willReturn(1);

        $surfaceValue->getAttribute()->willReturn($surface);
        $surfaceValue->getData()->willReturn($surfaceMetric);
        $surfaceValue->getLocale()->shouldNotBeCalled();
        $surfaceValue->getScope()->shouldNotBeCalled();
        $surface->getCode()->willReturn('surface');

        $surfaceMetric->getFamily()->willReturn('Surface');
        $surfaceMetric->getUnit()->willReturn('METER_SQUARE');
        $surfaceMetric->getData()->willReturn(10);

        $nameValue->getAttribute()->willReturn($name);
        $nameValue->getData()->willReturn('foobar');
        $nameValue->getLocale()->shouldNotBeCalled();
        $nameValue->getScope()->shouldNotBeCalled();

        $product->getValues()->willReturn([$weightValue, $surfaceValue, $nameValue]);

        $converter->setFamily('Weight')->shouldBeCalled()->willReturn($converter);
        $converter->convert('KILOGRAM', 'GRAM', 1)->willReturn(1000);

        $converter->setFamily('Surface')->shouldNotBeCalled();

        $productBuilder->addOrReplaceValue(Argument::cetera())->shouldBeCalledTimes(1);
        $productBuilder
            ->addOrReplaceValue($product, $weight, null, null, ['amount' => 1000, 'unit' => 'GRAM'])
            ->shouldBeCalled();

        $this->convert($product, $channel);
    }

    function it_does_not_convert_null_metric_values_in_the_channel(
        $converter,
        $productBuilder,
        ValueInterface $weightValue,
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
        $productBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->convert($product, $channel);
    }
}
