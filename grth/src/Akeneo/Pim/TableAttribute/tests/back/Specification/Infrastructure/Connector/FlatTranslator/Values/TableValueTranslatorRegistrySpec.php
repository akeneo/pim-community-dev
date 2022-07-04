<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslatorRegistry;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class TableValueTranslatorRegistrySpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        TableValueTranslator $selectValueTranslator,
        TableValueTranslator $booleanValueTranslator,
        TableValueTranslator $measurementValueTranslator
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            self::getIngredientColumn(),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            self::getIsAllergenicColumn(),
            self::getLengthColumn(),
        ]));

        $selectValueTranslator->getSupportedColumnDataType()->willReturn(SelectColumn::DATATYPE);
        $booleanValueTranslator->getSupportedColumnDataType()->willReturn(BooleanColumn::DATATYPE);
        $measurementValueTranslator->getSupportedColumnDataType()->willReturn(MeasurementColumn::DATATYPE);
        $this->beConstructedWith(
            $tableConfigurationRepository,
            [$selectValueTranslator, $booleanValueTranslator, $measurementValueTranslator]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableValueTranslatorRegistry::class);
    }

    function it_translates_a_select_column(TableValueTranslator $selectValueTranslator)
    {
        $selectValueTranslator->translate('nutrition', self::getIngredientColumn(), 'en_US', 'salt')
            ->willReturn('Salt');
        $this->translate('nutrition', 'ingredient', 'en_US', 'salt')->shouldReturn('Salt');

        $selectValueTranslator->translate('nutrition', self::getIngredientColumn(), 'fr_FR', 'salt')
            ->willReturn('Sel');
        $this->translate('nutrition', 'ingredient', 'fr_FR', 'salt')->shouldReturn('Sel');
    }

    function it_translates_a_boolean_column(TableValueTranslator $booleanValueTranslator)
    {
        $booleanValueTranslator->translate('nutrition', self::getIsAllergenicColumn(), 'en_US', '1')
            ->shouldBeCalled()->willReturn('Yes');
        $this->translate('nutrition', 'is_allergenic', 'en_US', '1')->shouldReturn('Yes');

        $booleanValueTranslator->translate('nutrition', self::getIsAllergenicColumn(), 'fr_FR', '0')
            ->shouldBeCalled()->willReturn('No');
        $this->translate('nutrition', 'is_allergenic', 'fr_FR', '0')->shouldReturn('No');
    }

    function it_translates_a_measurement_value(TableValueTranslator $measurementValueTranslator)
    {
        $measurementValueTranslator->translate('nutrition', self::getLengthColumn(), 'en_US', '0.12 METER')
            ->shouldBeCalled()->willReturn('0.12 Meter');
        $this->translate('nutrition', '1', 'en_US', '0.12 METER');

        $measurementValueTranslator->translate('nutrition', self::getLengthColumn(), 'fr_FR', '12 CENTIMETER')
                                   ->shouldBeCalled()->willReturn('12 Centimètre');
        $this->translate('nutrition', '1', 'fr_FR', '12 CENTIMETER');
    }

    function it_cannot_translate_when_column_type_is_not_handled()
    {
        $this->translate('nutrition', 'quantity', 'en_US', '12')->shouldReturn('12');
    }

    function it_cannot_translate_when_column_is_unknown()
    {
        $this->translate('nutrition', 'unknown', 'en_US', 'foo')->shouldReturn('foo');
    }

    private static function getIngredientColumn(): SelectColumn
    {
        return SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]);
    }

    private static function getIsAllergenicColumn(): BooleanColumn
    {
        return BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']);
    }

    private static function getLengthColumn(): MeasurementColumn
    {
        return MeasurementColumn::fromNormalized(
            [
                'id' => ColumnIdGenerator::length(),
                'code' => '1',
                'measurement_family_code' => 'Length',
                'measurement_default_unit_code' => 'METER',
            ]
        );
    }
}
