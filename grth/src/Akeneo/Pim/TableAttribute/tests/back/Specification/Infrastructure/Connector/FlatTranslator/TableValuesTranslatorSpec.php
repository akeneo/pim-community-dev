<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\AttributeTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableColumnTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableValuesTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslatorRegistry;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TableValuesTranslatorSpec extends ObjectBehavior
{
    function let(
        TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        AttributeTranslator $attributeTranslator,
        TableColumnTranslator $tableColumnTranslator,
        TranslatorInterface $translator
    ) {
        $translator->trans('pim_table.export_with_label.product', [], null, 'en_US')->willReturn('Product');
        $translator->trans('pim_table.export_with_label.product_model', [], null, 'en_US')->willReturn('Product model');
        $translator->trans('pim_table.export_with_label.attribute', [], null, 'en_US')->willReturn('Attribute');

        $tableColumnTranslator->getTableColumnLabels('nutrition', 'en_US', ['Product', 'Product model', 'Attribute'])
            ->willReturn([
                'ingredient' => 'Ingredient',
                'quantity' => '[quantity]',
                'is_allergenic' => 'Is allergenic',
            ]);

        $this->beConstructedWith(
            $tableValueTranslatorRegistry,
            $attributeTranslator,
            $tableColumnTranslator,
            $translator
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableValuesTranslator::class);
    }

    function it_translates_attribute_values(
        TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        AttributeTranslator $attributeTranslator
    ) {
        $items = [
            [
                'product' => '1',
                'attribute' => 'nutrition-fr_FR-eco',
                'ingredient' => 'salt',
                'quantity' => '12',
                'is_allergenic' => '1',
            ],
            [
                'product' => '1',
                'attribute' => 'nutrition-fr_FR-eco',
                'ingredient' => 'sugar',
                'quantity' => '5',
            ],
        ];

        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'salt')->willReturn('Salt');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', '12')->willReturn('12');
        $tableValueTranslatorRegistry->translate('nutrition', 'is_allergenic', 'en_US', '1')->willReturn('Yes');
        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'sugar')->willReturn('Sugar');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', '5')->willReturn('5');

        $attributeTranslator->translate('nutrition-fr_FR-eco', 'en_US')->willReturn('Nutrition (Français, ecommerce)');

        $this->translate($items, 'en_US', false)->shouldReturn([
            [
                'product' => '1',
                'attribute' => 'Nutrition (Français, ecommerce)',
                'ingredient' => 'Salt',
                'quantity' => '12',
                'is_allergenic' => 'Yes',
            ],
            [
                'product' => '1',
                'attribute' => 'Nutrition (Français, ecommerce)',
                'ingredient' => 'Sugar',
                'quantity' => '5',
            ],
        ]);
    }


    function it_translates_attribute_values_with_headers(
        TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        AttributeTranslator $attributeTranslator
    ) {
        $items = [
            [
                'product_model' => '1',
                'attribute' => 'nutrition-fr_FR-eco',
                'ingredient' => 'salt',
                'quantity' => '12',
                'is_allergenic' => '0',
            ],
            [
                'product_model' => '1',
                'attribute' => 'nutrition-fr_FR-eco',
                'ingredient' => 'sugar',
                'quantity' => '5',
            ],
        ];

        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'salt')->willReturn('Salt');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', '12')->willReturn('12');
        $tableValueTranslatorRegistry->translate('nutrition', 'is_allergenic', 'en_US', '0')->willReturn('No');
        $tableValueTranslatorRegistry->translate('nutrition', 'ingredient', 'en_US', 'sugar')->willReturn('Sugar');
        $tableValueTranslatorRegistry->translate('nutrition', 'quantity', 'en_US', '5')->willReturn('5');

        $attributeTranslator->translate('nutrition-fr_FR-eco', 'en_US')->willReturn('Nutrition (Français, ecommerce)');

        $this->translate($items, 'en_US', true)->shouldReturn([
            [
                'Product model' => '1',
                'Attribute' => 'Nutrition (Français, ecommerce)',
                'Ingredient' => 'Salt',
                '[quantity]' => '12',
                'Is allergenic' => 'No',
            ],
            [
                'Product model' => '1',
                'Attribute' => 'Nutrition (Français, ecommerce)',
                'Ingredient' => 'Sugar',
                '[quantity]' => '5',
            ],
        ]);
    }
}
