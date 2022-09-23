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

namespace Akeneo\Platform\Syndication\Application\MapValues;

use Akeneo\Platform\Syndication\Application\MapValues\FormatApplier\FormatApplier;
use Akeneo\Platform\Syndication\Application\MapValues\OperationApplier\OperationApplier;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplier;

class MapValuesQueryHandler
{
    private OperationApplier $operationApplier;
    private SelectionApplier $selectionApplier;
    private FormatApplier $formatApplier;

    public function __construct(
        OperationApplier $operationApplier,
        SelectionApplier $selectionApplier,
        FormatApplier $formatApplier
    ) {
        $this->operationApplier = $operationApplier;
        $this->selectionApplier = $selectionApplier;
        $this->formatApplier = $formatApplier;
    }

    public function handle(
        MapValuesQuery $mapValuesQuery
    ): array {
        $mappedProduct = [];

        foreach ($mapValuesQuery->getColumnCollection() as $dataMapping) {
            $mappedValues = [];

            $target = $dataMapping->getTarget();
            foreach ($dataMapping->getSourceCollection() as $source) {
                $operations = $source->getOperationCollection();
                $value = $mapValuesQuery->getValueCollection()->getFromSource($source);

                $transformedValue = $this->operationApplier->applyOperations(
                    $operations,
                    $value
                );

                $mappedValues[$source->getUuid()] = $this->selectionApplier->applySelection(
                    $source->getSelection(),
                    $target,
                    $transformedValue
                );
            }

            if (empty($mappedValues)) {
                continue;
            }

            $mappedProduct[$dataMapping->getTarget()->getName()] = $this->formatApplier->applyFormat(
                $dataMapping->getFormat(),
                $mappedValues
            );
        }

        return $mappedProduct;
    }
}
