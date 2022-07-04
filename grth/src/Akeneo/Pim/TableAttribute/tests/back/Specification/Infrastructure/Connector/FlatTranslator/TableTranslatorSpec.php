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
            ['ingredient' => 'butter', 'weight' => ['amount' => 3, 'unit' => 'GRAM']],
        ]);
        $table2 = \json_encode([
            ['ingredient' => 'salt', 'quantity' => 12, 'allergenic' => true],
        ]);

        $tableValueTranslatorRegistry
            ->translate('nutrition', 'ingredient', 'en_US', 'sugar')
            ->shouldBeCalled()->willReturn('Sugar');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'quantity', 'en_US', '50')
            ->shouldBeCalled()->willReturn('50');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'ingredient', 'en_US', 'pepper')
            ->shouldBeCalled()->willReturn('Pepper');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'quantity', 'en_US', '10')
            ->shouldBeCalled()->willReturn('10');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'ingredient', 'en_US', 'butter')
            ->shouldBeCalled()->willReturn('Butter');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'weight', 'en_US', '3 GRAM')
            ->shouldBeCalled()->willReturn('3 Gram');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'ingredient', 'en_US', 'salt')
            ->shouldBeCalled()->willReturn('[salt]');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'quantity', 'en_US', '12')
            ->shouldBeCalled()->willReturn('12');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'allergenic', 'en_US', '1')
            ->shouldBeCalled()->willReturn('Yes');

        $tableColumnTranslator->getTableColumnLabels('nutrition', 'en_US')->willReturn([
            'ingredient' => 'Ingredient US',
            'quantity' => 'Quantity',
            'allergenic' => '[allergenic]',
            'weight' => 'Weight'
        ]);

        $this->translate('nutrition', [], [$table1, $table2], 'en_US')->shouldBe([
            \json_encode([
                ['Ingredient US' => 'Sugar', 'Quantity' => '50'],
                ['Ingredient US' => 'Pepper', 'Quantity' => '10'],
                ['Ingredient US' => 'Butter', 'Weight' => '3 Gram'],
            ], JSON_UNESCAPED_UNICODE),
            \json_encode([
                ['Ingredient US' => '[salt]', 'Quantity' => '12', '[allergenic]' => 'Yes'],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $tableValueTranslatorRegistry
            ->translate('nutrition', 'ingredient', 'fr_FR', 'sugar')
            ->shouldBeCalled()->willReturn('Sucre');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'quantity', 'fr_FR', '50')
            ->shouldBeCalled()->willReturn('50');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'ingredient', 'fr_FR', 'pepper')
            ->shouldBeCalled()->willReturn('Poivre');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'quantity', 'fr_FR', '10')
            ->shouldBeCalled()->willReturn('10');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'ingredient', 'fr_FR', 'butter')
            ->shouldBeCalled()->willReturn('Beurre');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'weight', 'fr_FR', '3 GRAM')
            ->shouldBeCalled()->willReturn('3 [GRAM]');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'ingredient', 'fr_FR', 'salt')
            ->shouldBeCalled()->willReturn('Sel');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'quantity', 'fr_FR', '12')
            ->shouldBeCalled()->willReturn('12');
        $tableValueTranslatorRegistry
            ->translate('nutrition', 'allergenic', 'fr_FR', '1')
            ->shouldBeCalled()->willReturn('Vrai');

        $tableColumnTranslator->getTableColumnLabels('nutrition', 'fr_FR')->willReturn([
            'ingredient' => '[ingredient]',
            'quantity' => 'Quantité',
            'allergenic' => '[allergenic]',
            'weight' => '[weight]',
        ]);

        $this->translate('nutrition', [], [$table1, $table2], 'fr_FR')->shouldBe([
            \json_encode([
                ['[ingredient]' => 'Sucre', 'Quantité' => '50'],
                ['[ingredient]' => 'Poivre', 'Quantité' => '10'],
                ['[ingredient]' => 'Beurre', '[weight]' => '3 [GRAM]'],
            ], JSON_UNESCAPED_UNICODE),
            \json_encode([
                ['[ingredient]' => 'Sel', 'Quantité' => '12', '[allergenic]' => 'Vrai'],
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }
}
