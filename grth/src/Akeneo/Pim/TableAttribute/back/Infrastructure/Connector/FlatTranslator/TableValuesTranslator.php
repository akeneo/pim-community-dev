<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslatorRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class TableValuesTranslator
{
    public function __construct(
        private TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        private AttributeTranslator $attributeTranslator,
        private TableColumnTranslator $tableColumnTranslator,
        private TranslatorInterface $translator
    ) {
    }

    /**
     * @param array<string[]> $items for example:
     *  [
     *      {
     *          "product": "foo",
     *          "attribute": "nutrition-en_US-ecommerce",
     *          "ingredient": "salt",
     *          "is_allergenic": "1"
     *      },
     *      ...
     *  ]
     * @return array for example (header with label activated):
     *  [
     *      {
     *          "Product": "foo",
     *          "Attribute": "Nutrition (English US, Ecommerce)",
     *          "Ingredient": "Salt",
     *          "Is allergenic": "Yes"
     *      },
     *      ...
     *  ]
     */
    public function translate(array $items, string $localeCode, bool $headerWithLabel): array
    {
        Assert::allIsArray($items);
        $attributeCodes = [];
        foreach ($items as $index => $item) {
            Assert::isMap($item);
            Assert::allString($item);
            $attributeParts = \explode('-', $item['attribute']);
            $attributeCodes[$index] = $attributeParts[0];
            $items[$index] = $this->translateItem($item, $localeCode);
        }

        return $headerWithLabel ? $this->translateWithHeaderLabel($items, $localeCode, $attributeCodes) : $items;
    }

    private function translateWithHeaderLabel(array $items, string $localeCode, array $attributeCodes): array
    {
        $extraColumnTranslations = [
            'product' => $this->translator->trans('pim_table.export_with_label.product', [], null, $localeCode),
            'product_model' => $this->translator->trans('pim_table.export_with_label.product_model', [], null, $localeCode),
            'attribute' => $this->translator->trans('pim_table.export_with_label.attribute', [], null, $localeCode),
        ];

        $newItems = [];
        foreach ($items as $index => $item) {
            $tableColumnLabels = $this->tableColumnTranslator->getTableColumnLabels(
                $attributeCodes[$index],
                $localeCode,
                \array_values($extraColumnTranslations)
            );

            $newItem = [];
            foreach ($item as $column => $value) {
                $label = \array_key_exists($column, $extraColumnTranslations)
                    ? $extraColumnTranslations[$column]
                    : ($tableColumnLabels[$column] ?? \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $column))
                ;
                $newItem[$label] = $value;
            }

            $newItems[] = $newItem;
        }

        return $newItems;
    }

    private function translateItem(array $item, string $localeCode): array
    {
        $attributeParts = \explode('-', $item['attribute']);
        $attributeCode = $attributeParts[0];

        foreach ($item as $column => $value) {
            if ('' === $value) {
                continue;
            }

            $item[$column] = match ($column) {
                'product', 'product_model' => $value,
                'attribute' => $this->attributeTranslator->translate($value, $localeCode),
                default => $this->tableValueTranslatorRegistry->translate($attributeCode, (string) $column, $localeCode, $value),
            };
        }

        return $item;
    }
}
