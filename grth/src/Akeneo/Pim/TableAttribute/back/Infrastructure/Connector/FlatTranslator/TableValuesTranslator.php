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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslatorRegistry;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class TableValuesTranslator
{
    private TableValueTranslatorRegistry $tableValueTranslatorRegistry;
    private AttributeColumnTranslator $attributeColumnTranslator;
    private TableConfigurationRepository $tableConfigurationRepository;
    private LabelTranslatorInterface $labelTranslator;
    private array $cachedColumnTranslations = [];

    public function __construct(
        TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        AttributeColumnTranslator $attributeColumnTranslator,
        TableConfigurationRepository $tableConfigurationRepository,
        LabelTranslatorInterface $labelTranslator
    ) {
        $this->tableValueTranslatorRegistry = $tableValueTranslatorRegistry;
        $this->attributeColumnTranslator = $attributeColumnTranslator;
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->labelTranslator = $labelTranslator;
    }

    public function translate(array $items, string $localeCode, bool $headerWithLabel): array
    {
        $attributeCodes = [];
        foreach ($items as $index => $item) {
            $attributeParts = \explode('-', $item['attribute']);
            $attributeCodes[$index] = $attributeParts[0];
            $items[$index] = $this->translateItem($item, $localeCode);
        }

        if (!$headerWithLabel) {
            return $items;
        }

        $newItems = [];
        foreach ($items as $index => $item) {
            $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attributeCodes[$index]);

            $newItem = [];
            foreach ($item as $column => $value) {
                // @TODO: handle doublons in the translations
                if (!\in_array($column, $this->cachedColumnTranslations)) {
                    $this->cachedColumnTranslations[$column] = $this->translateColumn(
                        $tableConfiguration,
                        $column,
                        $localeCode
                    );
                }

                $newItem[$this->cachedColumnTranslations[$column]] = $value;
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
                'attribute' => $this->attributeColumnTranslator->translate($value, $localeCode),
                // @TODO: handle doublons in the key
                default => $this->tableValueTranslatorRegistry->translate($attributeCode, $column, $localeCode, $value),
            };
        }

        return $item;
    }

    private function translateColumn(
        TableConfiguration $tableConfiguration,
        string $columnName,
        string $localeCode
    ): string {
        if (\in_array($columnName, ['product', 'product_model', 'attribute'])) {
            return $this->labelTranslator->translate(
                $columnName,
                $localeCode,
                \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $columnName)
            );
        }

        // @TODO: handle doublons in the key
        $column = $tableConfiguration->getColumnByCode(ColumnCode::fromString($columnName));
        if (null === $column) {
            return \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $columnName);
        }

        return $column->labels()->getLabel($localeCode)
            ?? \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $columnName);
    }
}
