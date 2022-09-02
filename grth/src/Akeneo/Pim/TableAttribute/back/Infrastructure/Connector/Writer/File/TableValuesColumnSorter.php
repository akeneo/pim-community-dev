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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\File;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableColumnTranslator;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class TableValuesColumnSorter implements ColumnSorterInterface
{
    /** @var string[] */
    private array $columnOrder = [
        'product',
        'product_model',
        'attribute',
    ];
    /** @var array<string, array<int, string>> */
    private array $translatedColumnOrder = [];

    public function __construct(
        private TableConfigurationRepository $tableConfigurationRepository,
        private TranslatorInterface $translator,
        private TableColumnTranslator $tableColumnTranslator
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function sort(array $unsortedColumns, array $context = []): array
    {
        $mainColumns = [];
        $valuesColumns = [];

        $mainColumnNames = $this->getSortedMainColumnNames($context);
        foreach ($unsortedColumns as $column) {
            if (\in_array($column, $mainColumnNames)) {
                $mainColumns[] = $column;
            } else {
                $valuesColumns[] = $column;
            }
        }

        \usort($mainColumns, $this->getCompareFunction($context));

        $tableAttributeCode = $context['filters']['table_attribute_code'];
        try {
            $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($tableAttributeCode);
        } catch (TableConfigurationNotFoundException) {
            return \array_merge($mainColumns, $valuesColumns);
        }

        $sortedTableColumns = $this->getSortedTableColumns($tableConfiguration, $tableAttributeCode, $context);

        $sortedValuesColumns = \array_intersect($sortedTableColumns, $valuesColumns);
        $sortedValuesColumns = \array_merge($sortedValuesColumns, \array_diff($valuesColumns, $sortedTableColumns));

        return \array_merge($mainColumns, $sortedValuesColumns);
    }

    private function getCompareFunction(array $context): callable
    {
        $columnOrder = $this->getSortedMainColumnNames($context);

        return fn (string $a, string $b) => \array_search($a, $columnOrder) - \array_search($b, $columnOrder);
    }

    /**
     * @return string[]
     */
    private function getSortedMainColumnNames(array $context): array
    {
        if (!($context['header_with_label'] ?? false)) {
            return $this->columnOrder;
        }

        Assert::stringNotEmpty($context['file_locale'] ?? null);
        $localeCode = $context['file_locale'];
        if (!\array_key_exists($localeCode, $this->translatedColumnOrder)) {
            $this->translatedColumnOrder[$localeCode] = [
                $this->translator->trans('pim_table.export_with_label.product', [], null, $localeCode),
                $this->translator->trans('pim_table.export_with_label.product_model', [], null, $localeCode),
                $this->translator->trans('pim_table.export_with_label.attribute', [], null, $localeCode),
            ];
        }

        return $this->translatedColumnOrder[$localeCode];
    }

    /**
     * @return string[]
     */
    private function getSortedTableColumns(
        TableConfiguration $tableConfiguration,
        string $attributeCode,
        array $context
    ): array {
        if (!($context['header_with_label'] ?? false)) {
            return \array_map(
                static fn (ColumnCode $columnCode): string => $columnCode->asString(),
                $tableConfiguration->columnCodes()
            );
        }

        Assert::stringNotEmpty($context['file_locale'] ?? null);
        $localeCode = $context['file_locale'];

        return $this->tableColumnTranslator->getTableColumnLabels(
            $attributeCode,
            $localeCode,
            $this->getSortedMainColumnNames($context)
        );
    }
}
