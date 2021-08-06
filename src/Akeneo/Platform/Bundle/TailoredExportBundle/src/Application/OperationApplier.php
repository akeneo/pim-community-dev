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

use Akeneo\Platform\TailoredExport\Application\OperationApplier\OperationApplierInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class OperationApplier
{
    /** @var iterable<OperationApplierInterface> */
    private iterable $operationAppliers;

    public function __construct(iterable $operationAppliers)
    {
        $this->operationAppliers = $operationAppliers;
    }

    /** Check how we scale this part (only have Applier corresponding to the attribute type or sort Applier ?) */
    public function applyOperations(
        OperationCollection $operationCollection,
        SourceValueInterface $value
    ): SourceValueInterface {
        foreach ($this->operationAppliers as $applier) {
            foreach ($operationCollection as $operation) {
                if (!$applier->supports($operation, $value)) {
                    continue;
                }

                $value = $applier->applyOperation($operation, $value);
            }
        }

        return $value;
    }
}
