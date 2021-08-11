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

use Akeneo\Platform\TailoredExport\Application\Common\Column\Column;
use Akeneo\Platform\TailoredExport\Application\MapValues\OperationApplier\OperationApplier;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplier;

class MapValuesQueryHandler
{
    private OperationApplier $operationApplier;
    private SelectionApplier $selectionApplier;

    public function __construct(
        OperationApplier $operationApplier,
        SelectionApplier $selectionApplier
    ) {
        $this->operationApplier = $operationApplier;
        $this->selectionApplier = $selectionApplier;
    }

    public function handle(
        MapValuesQuery $mapValuesQuery
    ): array {
        $mappedProduct = [];

        /** @var Column $column */
        foreach ($mapValuesQuery->getColumnCollection() as $column) {
            $mappedValues = [];
            foreach ($column->getSourceCollection() as $source) {
                $operations = $source->getOperationCollection();
                $value = $mapValuesQuery->getValueCollection()->getFromSource($source);

                $transformedValue = $this->operationApplier->applyOperations($operations, $value);
                $mappedValues[] = $this->selectionApplier->applySelection(
                    $source->getSelection(),
                    $transformedValue
                );
            }

            $mappedProduct[$column->getTarget()] = implode(' ', $mappedValues);
        }

        return $mappedProduct;
    }
}
