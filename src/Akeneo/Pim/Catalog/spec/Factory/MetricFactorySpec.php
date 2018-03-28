<?php

namespace spec\Pim\Component\Catalog\Factory;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException;
use Akeneo\Bundle\MeasureBundle\Exception\UnknownMeasureException;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MetricFactorySpec extends ObjectBehavior
{
    const METRIC_CLASS = 'Pim\Component\Catalog\Model\Metric';

    function let(MeasureConverter $measureConverter, MeasureManager $measureManager)
    {
        $this->beConstructedWith($measureConverter, $measureManager, self::METRIC_CLASS);
    }

    function it_creates_a_metric($measureConverter, $measureManager)
    {
        $measureConverter->setFamily('Weight')->willReturn($measureConverter);
        $measureConverter->convertBaseToStandard('GRAM', 42)->willReturn(0.042);

        $measureManager->getStandardUnitForFamily('Weight')->willReturn('KILOGRAM');

        $metric = $this->createMetric('Weight', 'GRAM', 42);

        $metric->shouldReturnAnInstanceOf(self::METRIC_CLASS);
        $metric->__toString()->shouldBeEqualTo('42.0000 GRAM');
        $metric->getFamily()->shouldBeEqualTo('Weight');
        $metric->getUnit()->shouldBeEqualTo('GRAM');
        $metric->getData()->shouldBeEqualTo(42);
        $metric->getBaseUnit()->shouldBeEqualTo('KILOGRAM');
        $metric->getBaseData()->shouldBeEqualTo(0.042);
    }

    function it_creates_a_metric_if_provided_data_is_null($measureConverter, $measureManager)
    {
        $measureConverter->setFamily('Weight')->shouldNotBeCalled();
        $measureConverter->convertBaseToStandard()->shouldNotBeCalled();

        $measureManager->getStandardUnitForFamily('Weight')->willReturn('KILOGRAM');

        $metric = $this->createMetric('Weight', 'GRAM', null);

        $metric->shouldReturnAnInstanceOf(self::METRIC_CLASS);
        $metric->__toString()->shouldBeEqualTo('');
        $metric->getFamily()->shouldBeEqualTo('Weight');
        $metric->getUnit()->shouldBeEqualTo('GRAM');
        $metric->getData()->shouldBeNull();
        $metric->getBaseUnit()->shouldBeEqualTo('KILOGRAM');
        $metric->getBaseData()->shouldBeNull();
    }

    function it_creates_a_metric_if_provided_data_is_not_numeric($measureConverter, $measureManager)
    {
        $measureConverter->setFamily('Weight')->willReturn($measureConverter);
        $measureConverter->convertBaseToStandard('GRAM', 'foobar')->willReturn(0.0000);

        $measureManager->getStandardUnitForFamily('Weight')->willReturn('KILOGRAM');

        $metric = $this->createMetric('Weight', 'GRAM', 'foobar');

        $metric->shouldReturnAnInstanceOf(self::METRIC_CLASS);
        $metric->__toString()->shouldBeEqualTo('0.0000 GRAM');
        $metric->getFamily()->shouldBeEqualTo('Weight');
        $metric->getUnit()->shouldBeEqualTo('GRAM');
        $metric->getData()->shouldBeEqualTo('foobar');
        $metric->getBaseUnit()->shouldBeEqualTo('KILOGRAM');
        $metric->getBaseData()->shouldBeEqualTo(0.0000);
    }

    function it_creates_an_invalid_metric_if_provided_unit_is_not_compatible_with_measure_family(
        $measureConverter,
        $measureManager
    ) {
        $measureConverter->setFamily('Length')->willReturn($measureConverter);
        $measureConverter
            ->convertBaseToStandard('GRAM', 42)
            ->shouldBeCalled()
            ->willThrow(UnknownMeasureException::class);

        $measureManager->getStandardUnitForFamily(Argument::any())->shouldNotBeCalled();

        $metric = $this->createMetric('Length', 'GRAM', 42);

        $metric->shouldReturnAnInstanceOf(self::METRIC_CLASS);
        $metric->__toString()->shouldBeEqualTo('42.0000 GRAM');
        $metric->getFamily()->shouldBeEqualTo('Length');
        $metric->getUnit()->shouldBeEqualTo('GRAM');
        $metric->getData()->shouldBeEqualTo(42);
        $metric->getBaseUnit()->shouldBeNull();
        $metric->getBaseData()->shouldBeNull();
    }

    function it_creates_an_invalid_metric_if_provided_measure_family_does_not_exists(
        $measureConverter,
        $measureManager
    ) {
        $measureConverter
            ->setFamily('FooBar')
            ->shouldBeCalled()
            ->willThrow(UnknownFamilyMeasureException::class);

        $measureConverter->convertBaseToStandard(Argument::any())->shouldNotBeCalled();
        $measureManager->getStandardUnitForFamily(Argument::any())->shouldNotBeCalled();

        $metric = $this->createMetric('FooBar', 'GRAM', 42);

        $metric->shouldReturnAnInstanceOf(self::METRIC_CLASS);
        $metric->__toString()->shouldBeEqualTo('42.0000 GRAM');
        $metric->getFamily()->shouldBeEqualTo('FooBar');
        $metric->getUnit()->shouldBeEqualTo('GRAM');
        $metric->getData()->shouldBeEqualTo(42);
        $metric->getBaseUnit()->shouldBeNull();
        $metric->getBaseData()->shouldBeNull();
    }
}
