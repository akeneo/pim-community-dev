<?php

namespace spec\Pim\Bundle\TransformBundle\Converter;

use PhpSpec\ObjectBehavior;
use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\AbstractMetric;

class MetricConverterSpec extends ObjectBehavior
{
    function let(MeasureConverter $converter)
    {
        $this->beConstructedWith($converter);
    }

    function it_converts_metric_values_given_the_configured_base_unit_in_the_channel(
        $converter,
        AbstractProductValue $weightValue,
        AbstractProductValue $surfaceValue,
        AbstractProductValue $nameValue,
        AbstractAttribute $weight,
        AbstractAttribute $surface,
        AbstractAttribute $name,
        AbstractMetric $weightMetric,
        AbstractMetric $surfaceMetric,
        ProductInterface $product,
        Channel $channel
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
}
