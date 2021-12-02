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

use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslatorRegistry;

class TableValuesTranslator
{
    private TableValueTranslatorRegistry $tableValueTranslatorRegistry;

    public function __construct(TableValueTranslatorRegistry $tableValueTranslatorRegistry)
    {
        $this->tableValueTranslatorRegistry = $tableValueTranslatorRegistry;
    }

    public function translate(array $items, string $localeCode, bool $headerWithLabel): array
    {
        foreach ($items as $index => $item) {
            $items[$index] = $this->translateItem($item, $localeCode);
        }

        if ($headerWithLabel) {
            // todo
        }

        return $items;
    }

    private function translateItem(array $item, string $localeCode): array
    {
        $attributeParts = \explode('-', $item['attribute']);
        $attributeCode = $attributeParts[0];

        foreach ($item as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $item[$key] = match ($key) {
                'product', 'product_model' => $value,
                'attribute' => $value, // TODO translate ? Quid des locales/channels?
                default => $this->tableValueTranslatorRegistry->translate($attributeCode, $key, $localeCode, $value),
            };
        }

        return $item;
    }
}
