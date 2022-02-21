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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\OperationApplier;

use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;

class OperationApplier
{
    /**
     * @param OperationApplierInterface[] $operationAppliers
     */
    public function __construct(
        private iterable $operationAppliers,
    ) {
    }

    /** Check how we scale this part (only have Applier corresponding to the attribute type or sort Applier ?) */
    public function applyOperations(
        OperationCollection $operationCollection,
        SourceValueInterface $value,
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
