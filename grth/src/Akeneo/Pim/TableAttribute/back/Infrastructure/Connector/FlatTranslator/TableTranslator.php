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

final class TableTranslator implements FlatAttributeValueTranslatorInterface
{
    private TableConfigurationRepository $tableConfigurationRepository;
    private TableValueTranslatorRegistry $tableValueTranslatorRegistry;
    private array $columnLabelsByAttributeCodeAndLocaleCode = [];

    /**
     * @param iterable<TableValueTranslator> $tableValueTranslators
     */
    public function __construct(
        TableConfigurationRepository $tableConfigurationRepository,
        TableValueTranslatorRegistry $tableValueTranslatorRegistry
    ) {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->tableValueTranslatorRegistry = $tableValueTranslatorRegistry;
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        return AttributeTypes::TABLE === $attributeType;
    }

    /**
     * Translate multiple json values
     *
     * @param string[] $values for example:
     *  [
     *      '[{"ingredient":"salt","is_allergenic":true}, ...]'
     *      '[{"ingredient":"sugar","is_allergenic":false}, ...]'
     *  ]
     * @return string[] for example:
     *  [
     *      '[{"Ingredient":"Salt","[is_allergenic]":"Yes"}, ...]'
     *      '[{"Ingredient":"[sugar]","[is_allergenic]":"No"}, ...]'
     *  ]
     *
     */
    public function translate(string $attributeCode, array $properties, array $values, string $locale): array
    {
        $indexedColumnLabels = $this->getIndexedColumnLabels($attributeCode, $locale);
        foreach ($values as $key => $value) {
            if ('' === $value) {
                continue;
            }

            $decodedValue = \json_decode($value, true);
            foreach ($decodedValue as $index => $row) {
                foreach ($row as $columnCode => $cellValue) {
                    $label = $indexedColumnLabels[$columnCode]
                        ?? \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $columnCode);
                    unset($decodedValue[$index][$columnCode]);
                    $decodedValue[$index][$label] = $this->tableValueTranslatorRegistry->translate(
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

    /**
     * @return array<string, string>
     */
    private function getIndexedColumnLabels(string $attributeCode, string $localeCode): array
    {
        $key = \sprintf('%s-%s', $attributeCode, $localeCode);
        if (!\array_key_exists($key, $this->columnLabelsByAttributeCodeAndLocaleCode)) {
            $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attributeCode);
            $indexedLabels = [];
            foreach ($tableConfiguration->columnIds() as $columnId) {
                $column = $tableConfiguration->getColumn($columnId);
                $indexedLabels[$column->code()->asString()] = $column->labels()->getLabel($localeCode)
                    ?? \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $column->code()->asString());
            }

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
}
