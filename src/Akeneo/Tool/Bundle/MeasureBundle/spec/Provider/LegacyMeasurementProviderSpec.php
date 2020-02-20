<?php

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Provider;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementAdapter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Yaml\Yaml;

class LegacyMeasurementProviderSpec extends ObjectBehavior
{
    function let(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        LegacyMeasurementAdapter $legacyMeasurementAdapter
    ) {
        $yaml = <<<YAML
measures_config:
    Length:
        standard: METER
        units:
            CENTIMETER:
                convert: [{'div': 0.01}]
                format: cm
            METER:
                convert: [{'test': 1}]
                format: m
    Weight:
        standard: GRAM
        units:
            MILLIGRAM:
                convert: [{'mul': 0.001}]
                symbol: mg
            GRAM:
                convert: [{'mul': 1}]
                symbol: g
            KILOGRAM:
                convert: [{'mul': 1000}]
                symbol: kg

YAML;

        $config = Yaml::parse($yaml);
        $this->beConstructedWith($measurementFamilyRepository, $legacyMeasurementAdapter);
    }

    public function it_returns_the_measurement_families(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        LegacyMeasurementAdapter $legacyMeasurementAdapter
    ) {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('Area'),
            LabelCollection::fromArray(["en_US" => "Area", "fr_FR" => "Surface"]),
            UnitCode::fromString('SQUARE_MILLIMETER'),
            [
                Unit::create(
                    UnitCode::fromString('SQUARE_MILLIMETER'),
                    LabelCollection::fromArray(["en_US" => "Square millimeter", "fr_FR" => "Millimètre carré"]),
                    [
                        Operation::create("mul", "0.000001"),
                        Operation::create("add", "4")
                    ],
                    "mm²",
                    ),
                Unit::create(
                    UnitCode::fromString('SQUARE_CENTIMETER'),
                    LabelCollection::fromArray(["en_US" => "Square centimeter", "fr_FR" => "Centimètre carré"]),
                    [Operation::create("mul", "0.0001")],
                    "cm²",
                    )
            ]
        );

        $measurementFamilies = [$measurementFamily];
        $legacyMeasurements = ['legacy measurements'];
        $measurementFamilyRepository->all()->willReturn(new \ArrayIterator($measurementFamilies));
        $legacyMeasurementAdapter->adapts($measurementFamily)->willReturn($legacyMeasurements);

        $this->getMeasurementFamilies()->shouldReturn($legacyMeasurements);
    }
}
