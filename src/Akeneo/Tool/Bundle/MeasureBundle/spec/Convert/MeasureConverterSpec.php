<?php

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Convert;

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Yaml\Yaml;

class MeasureConverterSpec extends ObjectBehavior
{
    function let(LegacyMeasurementProvider $provider): void
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

        $provider->getMeasurementFamilies()->willReturn($config['measures_config']);
        $this->beConstructedWith($provider);
    }

    public function it_allows_to_define_the_family(): void
    {
        $this->setFamily('Length')->shouldReturnAnInstanceOf(MeasureConverter::class);
        $this->setFamily('length')->shouldReturnAnInstanceOf(MeasureConverter::class);
    }

    public function it_throws_an_exception_if_an_unknown_family_is_set(): void
    {
        $this
            ->shouldThrow(
                new MeasurementFamilyNotFoundException()
            )
            ->during('setFamily', ['foo']);
    }

    public function it_converts_a_value_from_a_base_unit_to_a_final_unit(): void
    {
        $this->setFamily('Weight');
        $this->convert(
            'KILOGRAM',
            'MILLIGRAM',
            1
        )->shouldReturn('1000000.000000000000');
    }

    public function it_converts_a_value_from_a_base_unit_to_a_final_unit_case_insensitive(): void
    {
        $this->setFamily('weight');
        $this->convert(
            'kilogram',
            'milligram',
            1
        )->shouldReturn('1000000.000000000000');
    }

    public function it_converts_a_value_to_a_standard_unit(): void
    {
        $this->setFamily('Weight');
        $this->convertBaseToStandard('MILLIGRAM', 1000)->shouldReturn('1.000000000000');
        $this->convertBaseToStandard('milligram', 1000)->shouldReturn('1.000000000000');
    }

    public function it_converts_a_very_small_value_to_a_standard_unit(): void
    {
        $this->setFamily('Weight');
        $this->convertBaseToStandard(
            'KILOGRAM',
            1.0E-7
        )->shouldReturn('0.000100000000');
    }

    public function it_returns_zero_when_value_is_not_numeric(): void
    {
        $this->setFamily('Weight');
        $this->convertBaseToStandard(
            'KILOGRAM',
            '1.900.000'
        )->shouldReturn('0');
    }

    public function it_returns_zero_when_value_is_numeric_and_contains_space(): void
    {
        $this->setFamily('Weight');
        $this->convertBaseToStandard(
            'KILOGRAM',
            ' 1.900'
        )->shouldReturn('0');
    }

    public function it_converts_a_standard_value_to_a_final_unit(): void
    {
        $this->setFamily('Weight');
        $this->convertStandardToResult('KILOGRAM', 10)->shouldReturn('0.010000000000');
        $this->convertStandardToResult('KiloGram', 10)->shouldReturn('0.010000000000');
    }

    public function it_throws_an_exception_if_the_unit_measure_does_not_exist(): void
    {
        $this->setFamily('Weight');
        $this
            ->shouldThrow(
                new UnitNotFoundException(
                    'Could not find metric unit "foo" in family "Weight"'
                )
            )
            ->during('convertBaseToStandard', ['foo', Argument::any()]);

        $this
            ->shouldThrow(
                new UnitNotFoundException(
                    'Could not find metric unit "foo" in family "Weight"'
                )
            )
            ->during('convertStandardToResult', ['foo', Argument::any()]);
    }
}
