<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

class OperationHandler
{
    private iterable $operationHandlers;

    public function __construct(iterable $operationHandlers)
    {
        $this->operationHandlers = $operationHandlers;
    }

    public function handleOperations(array $operations, Attribute $attribute, ?ValueInterface $value)
    //TODO return: string
    {
        foreach ($this->operationHandlers as $operationHandler) {
            foreach ($operations as $operation) {
                if ($operationHandler->supports($operation, $attribute, $value)) {
                    $value = $operationHandler->handleOperation($operation, $attribute, $value);
                }
            }
        }

        return $value;
    }
}
