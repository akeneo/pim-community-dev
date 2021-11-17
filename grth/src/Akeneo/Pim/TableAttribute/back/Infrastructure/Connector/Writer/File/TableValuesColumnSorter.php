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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;

class TableValuesColumnSorter implements ColumnSorterInterface
{
    private array $columnOrder = [
        'product',
        'product_model',
        'attribute',
    ];
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function sort(array $unsortedColumns, array $context = []): array
    {
        $mainColumns = [];
        $valuesColumns = [];

        foreach ($unsortedColumns as $column) {
            if (\in_array($column, $this->columnOrder)) {
                $mainColumns[] = $column;
            } else {
                $valuesColumns[] = $column;
            }
        }

        \usort($mainColumns, [$this, 'compare']);

        $tableAttributeCode = $context['filters']['table_attribute_code'];
        try {
            $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($tableAttributeCode);
        } catch (TableConfigurationNotFoundException $e) {
            return \array_merge($mainColumns, $valuesColumns);
        }

        $sortedColumnCodes = \array_map(
            static fn (ColumnCode $columnCode): string => $columnCode->asString(),
            $tableConfiguration->columnCodes()
        );

        $sortedValuesColumns = \array_intersect($sortedColumnCodes, $valuesColumns);
        $sortedValuesColumns = \array_merge($sortedValuesColumns, \array_diff($valuesColumns, $sortedColumnCodes));

        return \array_merge($mainColumns, $sortedValuesColumns);
    }

    protected function compare(string $a, string $b): int
    {
        return \array_search($a, $this->columnOrder) - \array_search($b, $this->columnOrder);
    }
}
