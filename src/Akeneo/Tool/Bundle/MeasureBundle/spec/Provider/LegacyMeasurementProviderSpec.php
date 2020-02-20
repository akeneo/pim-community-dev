<?php

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Provider;

use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Yaml\Yaml;

class LegacyMeasurementProviderSpec extends ObjectBehavior
{
    function let(MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
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
        $this->beConstructedWith($config, $measurementFamilyRepository);
    }

    public function it_returns_the_measurement_families()
    {
        $this->getMeasurementFamilies()->shouldBeArray();
    }

}
