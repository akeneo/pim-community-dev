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
    private array $columnLabelsByAttributeCodeAndLocaleCode = [];

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
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
                foreach ($row as $stringCode => $cellValue) {
                    if (\array_key_exists($stringCode, $indexedColumnLabels)) {
                        unset($decodedValue[$index][$stringCode]);
                        $decodedValue[$index][$indexedColumnLabels[$stringCode]] = $cellValue;
                    }
                }
            }

            $values[$key] = \json_encode($decodedValue);
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
}
