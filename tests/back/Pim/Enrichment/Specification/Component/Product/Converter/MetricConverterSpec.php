<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Converter;

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Prophecy\Argument;


class MetricConverterSpec extends ObjectBehavior
{
    function let(
        MeasureConverter $converter,
        EntityWithValuesBuilderInterface $productBuilder,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository
    ) {
        $this->beConstructedWith($converter, $productBuilder, $attributeRepository, $measurementFamilyRepository);
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
        $attributeRepository,
        $measurementFamilyRepository
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
        $weightMetric->getSymbol()->willReturn('kg');

        $surfaceValue->getAttributeCode()->willReturn('surface');
        $surfaceValue->getData()->willReturn($surfaceMetric);
        $surfaceValue->getLocaleCode()->shouldNotBeCalled();
        $surfaceValue->getScopeCode()->shouldNotBeCalled();
        $surface->getCode()->willReturn('surface');

        $surfaceMetric->getFamily()->willReturn('Surface');
        $surfaceMetric->getUnit()->willReturn('METER_SQUARE');
        $surfaceMetric->getData()->willReturn(10);
        $surfaceMetric->getSymbol()->willReturn('m2');

        $nameValue->getAttributeCode()->willReturn('name');
        $nameValue->getData()->willReturn('foobar');
        $nameValue->getLocaleCode()->shouldNotBeCalled();
        $nameValue->getScopeCode()->shouldNotBeCalled();

        $product->getValues()->willReturn([$weightValue, $surfaceValue, $nameValue]);

        $converter->setFamily('Weight')->shouldBeCalled()->willReturn($converter);
        $converter->convert('KILOGRAM', 'GRAM', 1)->willReturn(1000);

        $converter->setFamily('Surface')->shouldNotBeCalled();

        $attributeRepository->findOneByIdentifier('weight')->willReturn($weight);

        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('Weight'),
            LabelCollection::fromArray([]),
            UnitCode::fromString('KILOGRAM'),
            [
                Unit::create(
                    UnitCode::fromString('KILOGRAM'),
                    LabelCollection::fromArray([]),
                    [
                        Operation::create('mul', '1'),
                    ],
                    'kg',
                ),
                Unit::create(
                    UnitCode::fromString('GRAM'),
                    LabelCollection::fromArray([]),
                    [Operation::create('mul', '0.0001')],
                    'g',
                )
            ]
        );
        $measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString('Weight'))
            ->willReturn($measurementFamily);

        $productBuilder->addOrReplaceValue(Argument::cetera())->shouldBeCalledTimes(1);
        $productBuilder
            ->addOrReplaceValue($product, $weight, null, null, ['amount' => 1000, 'unit' => 'GRAM', 'symbol' => 'g'])
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
