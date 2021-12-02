<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableValueTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableValueTranslatorRegistry;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class TableValueTranslatorRegistrySpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        TableValueTranslator $selectValueTranslator,
        TableValueTranslator $booleanValueTranslator
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            self::getIngredientColumn(),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            self::getIsAllergenicColumn(),
        ]));

        $selectValueTranslator->getSupportedColumnDataType()->willReturn(SelectColumn::DATATYPE);
        $booleanValueTranslator->getSupportedColumnDataType()->willReturn(BooleanColumn::DATATYPE);
        $this->beConstructedWith($tableConfigurationRepository, [$selectValueTranslator, $booleanValueTranslator]);
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
        $booleanValueTranslator->translate('nutrition', self::getIsAllergenicColumn(), 'en_US', true)
            ->willReturn('Yes');
        $this->translate('nutrition', 'is_allergenic', 'en_US', true)->shouldReturn('Yes');

        $booleanValueTranslator->translate('nutrition', self::getIsAllergenicColumn(), 'fr_FR', false)
            ->willReturn('No');
        $this->translate('nutrition', 'is_allergenic', 'fr_FR', false)->shouldReturn('No');
    }

    function it_cannot_translate_when_column_type_is_not_handled()
    {
        $this->translate('nutrition', 'quantity', 'en_US', 12)->shouldReturn(12);
    }

    function it_cannot_translate_when_column_is_unknown()
    {
        $this->translate('nutrition', 'unknown', 'en_US', 'foo')->shouldReturn('foo');
    }

    private static function getIngredientColumn(): SelectColumn
    {
        return SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient']);
    }

    private static function getIsAllergenicColumn(): BooleanColumn
    {
        return BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']);
    }
}
