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

namespace Akeneo\Platform\TailoredExport\Application\MapValues;

use Akeneo\Platform\TailoredExport\Application\MapValues\FormatApplier\FormatApplier;
use Akeneo\Platform\TailoredExport\Application\MapValues\OperationApplier\OperationApplier;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplier;

class MapValuesQueryHandler
{
    public function __construct(
        private OperationApplier $operationApplier,
        private SelectionApplier $selectionApplier,
        private FormatApplier $formatApplier,
    ) {
    }

    public function handle(
        MapValuesQuery $mapValuesQuery,
    ): array {
        $mappedProduct = [];

        foreach ($mapValuesQuery->getColumnCollection() as $column) {
            $mappedValues = [];

            foreach ($column->getSourceCollection() as $source) {
                $operations = $source->getOperationCollection();
                $value = $mapValuesQuery->getValueCollection()->getFromSource($source);

                $transformedValue = $this->operationApplier->applyOperations(
                    $operations,
                    $value,
                );

                $mappedValues[$source->getUuid()] = $this->selectionApplier->applySelection(
                    $source->getSelection(),
                    $transformedValue,
                );
            }

            $mappedProduct[$column->getTarget()] = $this->formatApplier->applyFormat(
                $column->getFormat(),
                $mappedValues,
            );
        }

        return $mappedProduct;
    }
}
