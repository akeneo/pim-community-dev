<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\StorageUtils\Repository\BaseCachedObjectRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class MetricPresenterSpec extends ObjectBehavior
{
    function let(
        NumberFactory $numberFactory,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        BaseCachedObjectRepository $baseCachedObjectRepository,
		LoggerInterface $logger
    ) {
        $this->beConstructedWith(
            $numberFactory,
            ['pim_catalog_metric'],
            $measurementFamilyRepository,
            $baseCachedObjectRepository,
			$logger
        );
    }

    function it_supports_metric()
    {
        $this->supports('pim_catalog_metric')->shouldReturn(true);
        $this->supports('foobar')->shouldReturn(false);
    }

    function it_presents_english_metric(
        NumberFactory $numberFactory,
        \NumberFormatter $numberFormatter,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        AttributeInterface $productAttribute,
        MeasurementFamily $measurementFamily,
        BaseCachedObjectRepository $baseCachedObjectRepository
    ) {
        $baseCachedObjectRepository->findOneByIdentifier('weight')->willReturn($productAttribute);
        $productAttribute->getMetricFamily()->willReturn('Weight');
        $measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString('Weight'))
            ->willReturn($measurementFamily);
        $measurementFamily->getUnitLabel(
            UnitCode::fromString('KILOGRAM'),
            LocaleIdentifier::fromCode('en_US'))
            ->willReturn('Kilogram');
        $numberFactory->create(['attribute' => 'weight', 'locale' => 'en_US'])->willReturn($numberFormatter);
        $numberFormatter->format(12000.34)->willReturn('12,000.34');
        $numberFormatter->setAttribute(Argument::any(), Argument::any())->willReturn(true);
        $this
            ->present(['amount' => 12000.34, 'unit' => 'KILOGRAM'], [
                'attribute' => 'weight',
                'locale' => 'en_US'
            ])
            ->shouldReturn('12,000.34 Kilogram');
    }

    function it_presents_zero_amount_metric(
        NumberFactory $numberFactory,
        BaseCachedObjectRepository $baseCachedObjectRepository,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        AttributeInterface $productAttribute,
        MeasurementFamily $measurementFamily,
        \NumberFormatter $numberFormatter
    )
    {
        $baseCachedObjectRepository->findOneByIdentifier('weight')->willReturn($productAttribute);
        $productAttribute->getMetricFamily()->willReturn('Weight');
        $measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString('Weight'))
            ->willReturn($measurementFamily);
        $measurementFamily->getUnitLabel(
            UnitCode::fromString('KILOGRAM'),
            LocaleIdentifier::fromCode('en_US'))
            ->willReturn('Kilogram');
        $numberFactory->create(['attribute' => 'weight', 'locale' => 'en_US'])->willReturn($numberFormatter);
        $numberFormatter->format(0.000)->willReturn('0');
        $numberFormatter->setAttribute(Argument::any(), Argument::any())->shouldNotBeCalled(); //todo ->willReturn(null);

        $this
            ->present(['amount' => 0.000, 'unit' => 'KILOGRAM'], [
                'attribute' => 'weight',
                'locale' => 'en_US'
            ])
            ->shouldReturn('0 Kilogram');
    }
}
