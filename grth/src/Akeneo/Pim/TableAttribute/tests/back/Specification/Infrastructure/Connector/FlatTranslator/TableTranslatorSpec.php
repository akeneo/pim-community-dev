<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableColumnTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslatorRegistry;
use PhpSpec\ObjectBehavior;

class TableTranslatorSpec extends ObjectBehavior
{
    function let(
        TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        TableColumnTranslator $tableColumnTranslator
    ) {
        $this->beConstructedWith($tableValueTranslatorRegistry, $tableColumnTranslator);
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
        TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        TableColumnTranslator $tableColumnTranslator
    ) {
        $table1 = \json_encode([
            ['ingredient' => 'sugar', 'quantity' => 50],
            ['ingredient' => 'pepper', 'quantity' => 10],
        ]);
        $table2 = \json_encode([
            ['ingredient' => 'salt', 'quantity' => 12, 'allergenic' => true],
        ]);

        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'sugar')->willReturn('Sugar');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', 50)->willReturn(50);
        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'pepper')->willReturn('Pepper');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', 10)->willReturn(10);
        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'salt')->willReturn('[salt]');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', 12)->willReturn(12);
        $tableValueTranslatorRegistry->translate('nutrition', 'allergenic', 'en_US', true)->willReturn('Oui');

        $tableColumnTranslator->getTableColumnLabels('nutrition', 'en_US')->willReturn([
            'ingredient' => 'Ingredient US',
            'quantity' => 'Quantity',
            'allergenic' => '[allergenic]',
        ]);

        $this->translate('nutrition', [], [$table1, $table2], 'en_US')->shouldBe([
            \json_encode([
                ['Ingredient US' => 'Sugar', 'Quantity' => 50],
                ['Ingredient US' => 'Pepper', 'Quantity' => 10],
            ], JSON_UNESCAPED_UNICODE),
            \json_encode([
                ['Ingredient US' => '[salt]', 'Quantity' => 12, '[allergenic]' => 'Oui'],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'fr_FR', 'sugar')->willReturn('Sucre');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'fr_FR', 50)->willReturn(50);
        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'fr_FR', 'pepper')->willReturn('Poivre');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'fr_FR', 10)->willReturn(10);
        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'fr_FR', 'salt')->willReturn('Sel');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'fr_FR', 12)->willReturn(12);
        $tableValueTranslatorRegistry->translate('nutrition', 'allergenic', 'fr_FR', true)->willReturn('Vrai');

        $tableColumnTranslator->getTableColumnLabels('nutrition', 'fr_FR')->willReturn([
            'ingredient' => '[ingredient]',
            'quantity' => 'Quantité',
            'allergenic' => '[allergenic]',
        ]);

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
}
