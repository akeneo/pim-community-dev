<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableMeasurementTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;
use PhpSpec\ObjectBehavior;

class TableMeasurementTranslatorSpec extends ObjectBehavior
{
    function let(GetUnitTranslations $getUnitTranslations)
    {
        $getUnitTranslations->byMeasurementFamilyCodeAndLocale('Length', 'en_US')->willReturn([
            'METER' => 'Meter',
            'KILOMETER' => 'Kilometer',
        ]);
        $getUnitTranslations->byMeasurementFamilyCodeAndLocale('Length', 'fr_FR')->willReturn([
            'METER' => 'Mètre',
        ]);

        $this->beConstructedWith($getUnitTranslations);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableMeasurementTranslator::class);
        $this->shouldImplement(TableValueTranslator::class);
    }

    function it_translates_a_measurement_value()
    {
        $column = $this->measurementColum();
        $this->translate('table', $column, 'en_US', '3.54 METER')->shouldReturn('3.54 Meter');
        $this->translate('table', $column, 'fr_FR', '3.54 METER')->shouldReturn('3.54 Mètre');
        $this->translate('table', $column, 'en_US', '0.11 KILOMETER')->shouldReturn('0.11 Kilometer');
        $this->translate('table', $column, 'fr_FR', '0.11 KILOMETER')->shouldReturn('0.11 [KILOMETER]');
    }

    function it_does_not_translate_invalid_measurement_values()
    {
        $this->translate('table', $this->measurementColum(), 'en_US', 'invalid')->shouldReturn('invalid');
        $this->translate('table', $this->measurementColum(), 'en_US', 'other invalid measurement')->shouldReturn('other invalid measurement');
    }

    private function measurementColum(): MeasurementColumn
    {
        return MeasurementColumn::fromNormalized([
            'id' => ColumnIdGenerator::length(),
            'code' => 'length',
            'measurement_family_code' => 'Length',
            'measurement_default_unit_code' => 'METER',
        ]);
    }
}
