<?php

namespace spec\AkeneoMeasureBundle\Provider;

use AkeneoMeasureBundle\Model\LabelCollection;
use AkeneoMeasureBundle\Model\MeasurementFamily;
use AkeneoMeasureBundle\Model\MeasurementFamilyCode;
use AkeneoMeasureBundle\Model\Operation;
use AkeneoMeasureBundle\Model\Unit;
use AkeneoMeasureBundle\Model\UnitCode;
use AkeneoMeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use AkeneoMeasureBundle\Provider\LegacyMeasurementAdapter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Yaml\Yaml;

class LegacyMeasurementProviderSpec extends ObjectBehavior
{
    function let(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        LegacyMeasurementAdapter $legacyMeasurementAdapter
    ) {
        $this->beConstructedWith($measurementFamilyRepository, $legacyMeasurementAdapter);
    }

    public function it_returns_the_measurement_families(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        LegacyMeasurementAdapter $legacyMeasurementAdapter
    ) {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('Area'),
            LabelCollection::fromArray(['en_US' => 'Area', 'fr_FR' => 'Surface']),
            UnitCode::fromString('SQUARE_MILLIMETER'),
            [
                Unit::create(
                    UnitCode::fromString('SQUARE_MILLIMETER'),
                    LabelCollection::fromArray(['en_US' => 'Square millimeter', 'fr_FR' => 'Millimètre carré']),
                    [
                        Operation::create('mul', '1'),
                    ],
                    'mm²',
                ),
                Unit::create(
                    UnitCode::fromString('SQUARE_CENTIMETER'),
                    LabelCollection::fromArray(['en_US' => 'Square centimeter', 'fr_FR' => 'Centimètre carré']),
                    [Operation::create('mul', '0.0001'), Operation::create('add', '4'),],
                    'cm²',
                )
            ]
        );

        $measurementFamilies = [$measurementFamily];
        $legacyMeasurements = ['legacy measurements'];
        $measurementFamilyRepository->all()->willReturn($measurementFamilies);
        $legacyMeasurementAdapter->adapts($measurementFamily)->willReturn($legacyMeasurements);

        $this->getMeasurementFamilies()->shouldReturn($legacyMeasurements);
    }
}
