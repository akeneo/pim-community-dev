<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\AttributeColumnTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableValuesTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslatorRegistry;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TableValuesTranslatorSpec extends ObjectBehavior
{
    function let(
        TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        AttributeColumnTranslator $attributeColumnTranslator,
        TableConfigurationRepository $tableConfigurationRepository,
        TranslatorInterface $translator
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => ['en_US' => 'Ingredient']]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic', 'labels' => ['en_US' => 'Is allergenic']]),
        ]));
        $translator->trans('pim_table.export_with_label.product', [], null, 'en_US')->willReturn('Product');
        $translator->trans('pim_table.export_with_label.product_model', [], null, 'en_US')->willReturn('Product model');
        $translator->trans('pim_table.export_with_label.attribute', [], null, 'en_US')->willReturn('Attribute');

        $this->beConstructedWith(
            $tableValueTranslatorRegistry,
            $attributeColumnTranslator,
            $tableConfigurationRepository,
            $translator
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableValuesTranslator::class);
    }

    function it_translates_attribute_values(
        TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        AttributeColumnTranslator $attributeColumnTranslator
    ) {
        $items = [
            [
                'product' => '1',
                'attribute' => 'nutrition-fr_FR-eco',
                'ingredient' => 'salt',
                'quantity' => 12,
                'is_allergenic' => true,
            ],
            [
                'product' => '1',
                'attribute' => 'nutrition-fr_FR-eco',
                'ingredient' => 'sugar',
                'quantity' => 5,
            ],
        ];

        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'salt')->willReturn('Salt');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', 12)->willReturn(12);
        $tableValueTranslatorRegistry->translate('nutrition', 'is_allergenic', 'en_US', true)->willReturn('Yes');
        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'sugar')->willReturn('Sugar');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', 5)->willReturn(5);

        $attributeColumnTranslator->translate('nutrition-fr_FR-eco', 'en_US')->willReturn('Nutrition (Français, ecommerce)');

        $this->translate($items, 'en_US', false)->shouldReturn([
            [
                'product' => '1',
                'attribute' => 'Nutrition (Français, ecommerce)',
                'ingredient' => 'Salt',
                'quantity' => 12,
                'is_allergenic' => 'Yes',
            ],
            [
                'product' => '1',
                'attribute' => 'Nutrition (Français, ecommerce)',
                'ingredient' => 'Sugar',
                'quantity' => 5,
            ],
        ]);
    }


    function it_translates_attribute_values_with_headers(
        TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        AttributeColumnTranslator $attributeColumnTranslator
    ) {
        $items = [
            [
                'product_model' => '1',
                'attribute' => 'nutrition-fr_FR-eco',
                'ingredient' => 'salt',
                'quantity' => 12,
                'is_allergenic' => false,
            ],
            [
                'product_model' => '1',
                'attribute' => 'nutrition-fr_FR-eco',
                'ingredient' => 'sugar',
                'quantity' => 5,
            ],
        ];

        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'salt')->willReturn('Salt');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', 12)->willReturn(12);
        $tableValueTranslatorRegistry->translate('nutrition', 'is_allergenic', 'en_US', false)->willReturn('No');
        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'sugar')->willReturn('Sugar');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', 5)->willReturn(5);

        $attributeColumnTranslator->translate('nutrition-fr_FR-eco', 'en_US')->willReturn('Nutrition (Français, ecommerce)');

        $this->translate($items, 'en_US', true)->shouldReturn([
            [
                'Product model' => '1',
                'Attribute' => 'Nutrition (Français, ecommerce)',
                'Ingredient' => 'Salt',
                '[quantity]' => 12,
                'Is allergenic' => 'No',
            ],
            [
                'Product model' => '1',
                'Attribute' => 'Nutrition (Français, ecommerce)',
                'Ingredient' => 'Sugar',
                '[quantity]' => 5,
            ],
        ]);
    }
}
