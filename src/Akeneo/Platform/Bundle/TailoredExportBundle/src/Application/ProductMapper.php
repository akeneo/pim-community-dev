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

namespace Akeneo\Platform\TailoredExport\Application;

use Akeneo\Platform\TailoredExport\Application\Query\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandler;
use Akeneo\Platform\TailoredExport\Domain\ValueCollection;

class ProductMapper
{
    private OperationApplier $operationApplier;
    private SelectionHandler $selectionHandler;

    public function __construct(
        OperationApplier $operationApplier,
        SelectionHandler $selectionHandler
    ) {
        $this->operationApplier = $operationApplier;
        $this->selectionHandler = $selectionHandler;
    }

    public function map(ColumnCollection $columnCollection, ValueCollection $valueCollection): array
    {
        $mappedProduct = [];

        /** @var Column $column */
        foreach ($columnCollection as $column) {
            $mappedValues = [];
            foreach ($column->getSourceCollection() as $source) {
                $operations = $source->getOperationCollection();
                $value = $valueCollection->getFromSource($source);

                $transformedValue = $this->operationApplier->applyOperations($operations, $value);
                $mappedValues[] = $this->selectionHandler->applySelection(
                    $source->getSelection(),
                    $transformedValue
                );
            }

            $mappedProduct[$column->getTarget()] = implode(' ', $mappedValues);
        }

        return $mappedProduct;
    }
}
