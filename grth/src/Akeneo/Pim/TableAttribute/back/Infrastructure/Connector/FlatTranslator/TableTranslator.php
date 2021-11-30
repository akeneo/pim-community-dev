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

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Webmozart\Assert\Assert;

final class TableTranslator implements FlatAttributeValueTranslatorInterface
{
    private TableConfigurationRepository $tableConfigurationRepository;
    /** @var iterable<TableValueTranslatorInterface> */
    private iterable $tableValueTranslators;
    private array $columnLabelsByAttributeCodeAndLocaleCode = [];

    /**
     * @param iterable<TableValueTranslatorInterface> $tableValueTranslators
     */
    public function __construct(
        TableConfigurationRepository $tableConfigurationRepository,
        iterable $tableValueTranslators
    ) {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        foreach ($tableValueTranslators as $tableValueTranslator) {
            Assert::isInstanceOf($tableValueTranslator, TableValueTranslatorInterface::class);
            $this->tableValueTranslators[$tableValueTranslator->getSupportedColumnDataType()] = $tableValueTranslator;
        }
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        return AttributeTypes::TABLE === $attributeType;
    }

    public function translate(string $attributeCode, array $properties, array $values, string $locale): array
    {
        $indexedColumnLabels = $this->getIndexedColumnLabels($attributeCode, $locale);
        foreach ($values as $key => $value) {
            $decodedValue = \json_decode($value, true);
            foreach ($decodedValue as $index => $row) {
                foreach ($row as $columnCode => $cellValue) {
                    $newKey = $columnCode;
                    if (\array_key_exists($columnCode, $indexedColumnLabels)) {
                        $newKey = $indexedColumnLabels[$columnCode];
                        unset($decodedValue[$index][$columnCode]);
                    }

                    $decodedValue[$index][$newKey] = $this->translateValue(
                        $attributeCode,
                        $columnCode,
                        $locale,
                        $cellValue
                    );
                }
            }

            $values[$key] = \json_encode($decodedValue, JSON_UNESCAPED_UNICODE);
        }

        return $values;
    }

    private function getIndexedColumnLabels(string $attributeCode, string $localeCode): array
    {
        $key = \sprintf('%s-%s', $attributeCode, $localeCode);
        if (!\array_key_exists($key, $this->columnLabelsByAttributeCodeAndLocaleCode)) {
            $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attributeCode);
            $indexedLabels = [];
            foreach ($tableConfiguration->columnIds() as $columnId) {
                $column = $tableConfiguration->getColumn($columnId);
                $indexedLabels[$column->code()->asString()] = $column->labels()->getLabel($localeCode);
            }

            $indexedLabels = \array_filter($indexedLabels);
            $duplicatedLabels = \array_diff_key($indexedLabels, \array_unique($indexedLabels));
            if ([] !== $duplicatedLabels) {
                foreach ($indexedLabels as $stringCode => $label) {
                    if (\in_array($label, $duplicatedLabels)) {
                        $indexedLabels[$stringCode] = \sprintf(
                            '%s%s%s',
                            $label,
                            FlatTranslatorInterface::COLUMN_CODE_AND_TRANSLATION_SEPARATOR,
                            $stringCode
                        );
                    }
                }
            }

            $this->columnLabelsByAttributeCodeAndLocaleCode[$key] = $indexedLabels;
        }

        return $this->columnLabelsByAttributeCodeAndLocaleCode[$key];
    }

    private function translateValue(
        string $attributeCode,
        string $columnCode,
        string $localeCode,
        mixed $cellValue
    ): mixed {
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attributeCode);
        $column = $tableConfiguration->getColumnByCode(ColumnCode::fromString($columnCode));
        if (null === $column) {
            return $cellValue;
        }

        $valueTranslator = $this->tableValueTranslators[$column->dataType()->asString()] ?? null;

        return null !== $valueTranslator
            ? $valueTranslator->translate($attributeCode, $column, $localeCode, $cellValue) ?? $cellValue
            : $cellValue
            ;
    }
}
