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

class TableColumnTranslator
{
    private TableConfigurationRepository $tableConfigurationRepository;
    /** @var array<string, array<string, string>> */
    private array $columnLabelsByAttributeCodeAndLocaleCode = [];

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    /**
     * @param string[] $forbiddenLabels
     * @return array<string, string>
     */
    public function getTableColumnLabels(string $attributeCode, string $localeCode, array $forbiddenLabels = []): array
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
            if ([] !== $duplicatedLabels || [] !== $forbiddenLabels) {
                foreach ($indexedLabels as $stringCode => $label) {
                    if (\in_array($label, $duplicatedLabels) || \in_array($label, $forbiddenLabels)) {
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
