<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use PhpSpec\ObjectBehavior;

class MeasurementFamilySpec extends ObjectBehavior
{
    private const MEASUREMENT_FAMILY_CODE = 'area';
    private const METER_UNIT_CODE = 'meter';
    private const METER_SYMBOL = 'm';
    private const CENTIMETER_UNIT_CODE = 'centimeter';
    private const CENTIMETER_SYMBOL = 'cm';

    function let()
    {
        $standardUnitCode = UnitCode::fromString(self::METER_UNIT_CODE);
        $meterUnit = Unit::create(
            $standardUnitCode,
            [],
            self::METER_SYMBOL
        );
        $centimeterUnit = Unit::create(
            UnitCode::fromString(self::CENTIMETER_UNIT_CODE),
            [],
            self::CENTIMETER_SYMBOL
        );
        $this->beConstructedThrough(
            'create',
            [
                MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
                $standardUnitCode,
                [$meterUnit, $centimeterUnit]
            ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementFamily::class);
    }

    function it_should_be_able_to_normalize_itself()
    {
        $this->normalize()->shouldReturn(
            [
                'code' => self::MEASUREMENT_FAMILY_CODE,
                'standard_unit_code' => self::METER_UNIT_CODE,
                'units' => [
                    [
                        'code' => self::METER_UNIT_CODE,
                        'convert_from_standard' => [],
                        'symbol' => self::METER_SYMBOL,
                    ],
                    [
                        'code' => self::CENTIMETER_UNIT_CODE,
                        'convert_from_standard' => [],
                        'symbol' => self::CENTIMETER_SYMBOL,
                    ]
                ]
            ]
        );
    }

    function it_should_not_be_able_create_a_measurement_family_having_no_units()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'create',
                [
                    MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
                    UnitCode::fromString(self::METER_UNIT_CODE),
                    []
                ]
            );
    }

    function it_should_not_be_able_create_a_measurement_family_having_a_standard_unit_not_being_in_the_units()
    {
        $unknownUnitCode = 'unknown_unit_code';
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'create',
                [
                    MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
                    UnitCode::fromString($unknownUnitCode),
                    [
                        Unit::create(
                            UnitCode::fromString(self::METER_UNIT_CODE),
                            [],
                            self::METER_SYMBOL
                        ),
                        Unit::create(
                            UnitCode::fromString(self::CENTIMETER_SYMBOL),
                            [],
                            self::CENTIMETER_SYMBOL
                        ),
                    ]
                ]
            );
    }
}
