<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Converter;

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Prophecy\Argument;


class MetricConverterSpec extends ObjectBehavior
{
    function let(
        MeasureConverter $converter,
        EntityWithValuesBuilderInterface $productBuilder,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($converter, $productBuilder, $attributeRepository);
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
        ChannelInterface $channel,
        $attributeRepository
    ) {
        $channel->getConversionUnits()->willReturn(['weight' => 'GRAM']);

        $weightValue->getAttributeCode()->willReturn('weight');
        $weightValue->getData()->willReturn($weightMetric);
        $weightValue->getLocaleCode()->willReturn(null);
        $weightValue->getScopeCode()->willReturn(null);
        $weight->getCode()->willReturn('weight');

        $weightMetric->getFamily()->willReturn('Weight');
        $weightMetric->getUnit()->willReturn('KILOGRAM');
        $weightMetric->getData()->willReturn(1);

        $surfaceValue->getAttributeCode()->willReturn('surface');
        $surfaceValue->getData()->willReturn($surfaceMetric);
        $surfaceValue->getLocaleCode()->shouldNotBeCalled();
        $surfaceValue->getScopeCode()->shouldNotBeCalled();
        $surface->getCode()->willReturn('surface');

        $surfaceMetric->getFamily()->willReturn('Surface');
        $surfaceMetric->getUnit()->willReturn('METER_SQUARE');
        $surfaceMetric->getData()->willReturn(10);

        $nameValue->getAttributeCode()->willReturn('name');
        $nameValue->getData()->willReturn('foobar');
        $nameValue->getLocaleCode()->shouldNotBeCalled();
        $nameValue->getScopeCode()->shouldNotBeCalled();

        $product->getValues()->willReturn([$weightValue, $surfaceValue, $nameValue]);

        $converter->setFamily('Weight')->shouldBeCalled()->willReturn($converter);
        $converter->convert('KILOGRAM', 'GRAM', 1)->willReturn(1000);

        $converter->setFamily('Surface')->shouldNotBeCalled();

        $attributeRepository->findOneByIdentifier('weight')->willReturn($weight);

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
        MetricInterface $weightMetric,
        ProductInterface $product,
        ChannelInterface $channel
    ) {
        $weightValue->getAttributeCode()->willReturn('weight');
        $weightValue->getData()->willReturn($weightMetric);

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
