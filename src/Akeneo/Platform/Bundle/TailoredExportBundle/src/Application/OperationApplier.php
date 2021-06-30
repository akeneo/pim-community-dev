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
use Akeneo\Platform\TailoredExport\Domain\OperationCollection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue;

class OperationApplier
{
    /** @var iterable<OperationApplierInterface> */
    private iterable $operationHandlers;

    public function __construct(iterable $operationHandlers)
    {
        $this->operationHandlers = $operationHandlers;
    }

    /** Check how we scale this part (only have handler corresponding to the attribute type or sort handler ?) */
    public function applyOperations(OperationCollection $operationCollection, SourceValue $value): SourceValue
    {
        foreach ($this->operationHandlers as $handler) {
            foreach ($operationCollection as $operation) {
                if (!$handler->supports($operation, $value)) {
                    continue;
                }

                $value = $handler->applyOperation($operation, $value);
            }
        }

        return $value;
    }
}