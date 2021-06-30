<?php

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Application;

use Akeneo\Platform\Bundle\TailoredExportBundle\src\Application\OperationApplier\PropertyOperationApplierInterface;
use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\OperationCollection;
use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue;

class PropertyOperationApplier
{
    /** @var iterable<PropertyOperationApplierInterface> */
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