<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableValueTranslatorInterface;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class TableTranslatorSpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        TableValueTranslatorInterface $selectValueTranslator,
        TableValueTranslatorInterface $booleanValueTranslator
    ) {
        $selectValueTranslator->getSupportedColumnDataType()->willReturn(SelectColumn::DATATYPE);
        $booleanValueTranslator->getSupportedColumnDataType()->willReturn(BooleanColumn::DATATYPE);
        $this->beConstructedWith($tableConfigurationRepository, [$selectValueTranslator, $booleanValueTranslator]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableTranslator::class);
        $this->shouldImplement(FlatAttributeValueTranslatorInterface::class);
    }

    function it_supports_only_table_attribute()
    {
        $this->supports(AttributeTypes::TABLE, 'foo')->shouldReturn(true);
        $this->supports(AttributeTypes::TABLE, 'bar')->shouldReturn(true);
        $this->supports(AttributeTypes::TEXT, 'bar')->shouldReturn(false);
    }

    function it_translates_the_columns_and_the_cell_values(
        TableConfigurationRepository $tableConfigurationRepository,
        TableValueTranslatorInterface $selectValueTranslator,
        TableValueTranslatorInterface $booleanValueTranslator
    ) {
        $ingredientColumn = SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => [
            'en_US' => 'Ingredient US',
        ]]);
        $quantityColumn = NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => [
            'fr_FR' => 'Quantité',
            'en_US' => 'Quantity',
        ]]);
        $allergenicColumn = BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'allergenic']);
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            $ingredientColumn,
            $quantityColumn,
            $allergenicColumn,
        ]));

        $table1 = \json_encode([
            ['ingredient' => 'sugar', 'quantity' => 50],
            ['ingredient' => 'pepper', 'quantity' => 10],
        ]);
        $table2 = \json_encode([
            ['ingredient' => 'salt', 'quantity' => 12, 'allergenic' => true],
        ]);

        $selectValueTranslator->translate('nutrition', $ingredientColumn, 'en_US', 'sugar')->willReturn('Sugar');
        $selectValueTranslator->translate('nutrition', $ingredientColumn, 'en_US', 'pepper')->willReturn('Pepper');
        $selectValueTranslator->translate('nutrition', $ingredientColumn, 'en_US', 'salt')->willReturn(null);
        $booleanValueTranslator->translate('nutrition', $allergenicColumn, 'en_US', true)->willReturn('Oui');

        $this->translate('nutrition', [], [$table1, $table2], 'en_US')->shouldBe([
            \json_encode([
                ['Ingredient US' => 'Sugar', 'Quantity' => 50],
                ['Ingredient US' => 'Pepper', 'Quantity' => 10],
            ], JSON_UNESCAPED_UNICODE),
            \json_encode([
                ['Ingredient US' => 'salt', 'Quantity' => 12, '[allergenic]' => 'Oui'],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $selectValueTranslator->translate('nutrition', $ingredientColumn, 'fr_FR', 'sugar')->willReturn('Sucre');
        $selectValueTranslator->translate('nutrition', $ingredientColumn, 'fr_FR', 'pepper')->willReturn('Poivre');
        $selectValueTranslator->translate('nutrition', $ingredientColumn, 'fr_FR', 'salt')->willReturn('Sel');
        $booleanValueTranslator->translate('nutrition', $allergenicColumn, 'fr_FR', true)->willReturn('Vrai');

        $this->translate('nutrition', [], [$table1, $table2], 'fr_FR')->shouldBe([
            \json_encode([
                ['[ingredient]' => 'Sucre', 'Quantité' => 50],
                ['[ingredient]' => 'Poivre', 'Quantité' => 10],
            ], JSON_UNESCAPED_UNICODE),
            \json_encode([
                ['[ingredient]' => 'Sel', 'Quantité' => 12, '[allergenic]' => 'Vrai'],
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }

    function it_translates_the_columns_with_duplicate(
        TableConfigurationRepository $tableConfigurationRepository,
        TableValueTranslatorInterface $selectValueTranslator
    ) {
        $ingredientColumn = SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => [
            'en_US' => 'Ingredient US',
        ]]);
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            $ingredientColumn,
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => [
                'fr_FR' => 'Quantité',
                'en_US' => 'Quantity',
            ]]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('other_quantity'), 'code' => 'other_quantity', 'labels' => [
                'fr_FR' => 'Quantité',
                'en_US' => 'Quantity',
            ]]),
        ]));

        $table1 = \json_encode([
            ['ingredient' => 'sugar', 'quantity' => 50, 'other_quantity' => 20],
            ['ingredient' => 'pepper', 'quantity' => 10],
        ]);

        $selectValueTranslator->translate('nutrition', $ingredientColumn, 'en_US', 'sugar')->willReturn('Sugar');
        $selectValueTranslator->translate('nutrition', $ingredientColumn, 'en_US', 'pepper')->willReturn('Pepper');

        $this->translate('nutrition', [], [$table1], 'en_US')->shouldBe([
            \json_encode([
                ['Ingredient US' => 'Sugar', 'Quantity--quantity' => 50, 'Quantity--other_quantity' => 20],
                ['Ingredient US' => 'Pepper', 'Quantity--quantity' => 10],
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }
}
