<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use PhpSpec\ObjectBehavior;

class MeasurementFamilySpec extends ObjectBehavior
{
    private const MEASUREMENT_FAMILY_CODE = 'area';
    private const MEASUREMENT_FAMILY_LABEL = ['fr_FR' => 'Aire', 'en_US' => 'area'];
    private const METER_UNIT_CODE = 'meter';
    private const METER_SYMBOL = 'm';
    private const CENTIMETER_UNIT_CODE = 'centimeter';
    private const CENTIMETER_SYMBOL = 'cm';
    private const METER_LABELS = ['fr_FR' => 'Mètre', 'en_US' => 'Meter'];
    private const CENTIMETER_LABELS = ['fr_FR' => 'centimètre', 'en_US' => 'mètre'];

    function let()
    {
        $standardUnitCode = UnitCode::fromString(self::METER_UNIT_CODE);
        $meterUnit = Unit::create(
            $standardUnitCode,
            LabelCollection::fromArray(self::METER_LABELS),
            [Operation::create('mul', '1')],
            self::METER_SYMBOL,
            );
        $centimeterUnit = Unit::create(
            UnitCode::fromString(self::CENTIMETER_UNIT_CODE),
            LabelCollection::fromArray(self::CENTIMETER_LABELS),
            [Operation::create('mul', '5')],
            self::CENTIMETER_SYMBOL,
            );
        $this->beConstructedThrough(
            'create',
            [
                MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
                LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
                $standardUnitCode,
                [$meterUnit, $centimeterUnit]
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementFamily::class);
    }

    function it_should_be_able_to_normalize_itself()
    {
        $this->normalize()->shouldReturn(
            [
                'code'               => self::MEASUREMENT_FAMILY_CODE,
                'labels'             => self::MEASUREMENT_FAMILY_LABEL,
                'standard_unit_code' => self::METER_UNIT_CODE,
                'units'              => [
                    [
                        'code'                  => self::METER_UNIT_CODE,
                        'labels'                => self::METER_LABELS,
                        'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                        'symbol'                => self::METER_SYMBOL,
                    ],
                    [
                        'code'                  => self::CENTIMETER_UNIT_CODE,
                        'labels'                => self::CENTIMETER_LABELS,
                        'convert_from_standard' => [['operator' => 'mul', 'value' => '5']],
                        'symbol'                => self::CENTIMETER_SYMBOL,
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
                    LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
                    UnitCode::fromString(self::METER_UNIT_CODE),
                    []
                ]
            );
    }

    function it_should_not_be_able_to_create_a_measurement_family_having_a_standard_unit_not_being_in_the_units()
    {
        $unknownUnitCode = 'unknown_unit_code';
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'create',
                [
                    MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
                    LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
                    UnitCode::fromString($unknownUnitCode),
                    [
                        Unit::create(
                            UnitCode::fromString(self::METER_UNIT_CODE),
                            LabelCollection::fromArray(self::METER_LABELS),
                            [Operation::create('mul', '1')],
                            self::METER_SYMBOL
                        ),
                        Unit::create(
                            UnitCode::fromString(self::CENTIMETER_SYMBOL),
                            LabelCollection::fromArray(self::CENTIMETER_LABELS),
                            [Operation::create('mul', '1')],
                            self::CENTIMETER_SYMBOL
                        ),
                    ]
                ]
            );
    }

    function it_should_not_be_able_to_create_if_there_are_unit_duplicates()
    {
        $meterUnit = Unit::create(
            UnitCode::fromString(self::METER_UNIT_CODE),
            LabelCollection::fromArray(self::METER_LABELS),
            [Operation::create('mul', '1')],
            self::METER_SYMBOL
        );
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'create',
                [
                    MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
                    LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
                    $meterUnit->code(),
                    [$meterUnit, $meterUnit]
                ]
            );
    }

    function it_returns_the_label_of_the_provided_unit_for_the_provided_locale()
    {
        $this->getUnitLabel(
            UnitCode::fromString(self::CENTIMETER_UNIT_CODE),
            LocaleIdentifier::fromCode('fr_FR')
        )->shouldReturn('centimètre');
    }

    function it_should_throw_when_the_provided_unit_is_not_found()
    {
        $this->shouldThrow(UnitNotFoundException::class)
            ->during(
                'getUnitLabel',
                [
                    UnitCode::fromString('UNKNOWN'),
                    LocaleIdentifier::fromCode('fr_FR')
                ]
            );
    }

    function it_should_not_be_able_to_create_if_the_standard_unit_conversion_is_not_a_multiply_by_one()
    {
        $invalidStandardUnitOperation = Operation::create('mul', '5');
        $this->shouldThrow(\Exception::class)
            ->during(
                'create',
                [
                    MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
                    LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
                    UnitCode::fromString('invalid_standard_unit_code'),
                    [
                        Unit::create(
                            UnitCode::fromString('invalid_standard_unit_code'),
                            LabelCollection::fromArray([]),
                            [$invalidStandardUnitOperation],
                            ''
                        )
                    ]
                ]
            );
    }
}
