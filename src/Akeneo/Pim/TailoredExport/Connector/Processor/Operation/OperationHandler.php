<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class OperationHandler implements OperationHandlerInterface
{
    private iterable $operationHandlers;

    public function __construct(iterable $operationHandlers)
    {
        $this->operationHandlers = $operationHandlers;
    }

    public function handleOperation(array $operation, AttributeInterface $attribute, $value)
    {
        foreach ($this->operationHandlers as $operationHandler) {
            if ($operationHandler->supports($operation, $attribute, $value)) {
                $value = $operationHandler->handleOperation($operation, $attribute, $value);
            }
        }

        return $value;
    }

    public function supports(array $operation, AttributeInterface $attribute, $value)
    {
        foreach ($this->operationHandlers as $operationHandler) {
            if ($operationHandler->supports($operation, $attribute, $value)) {
                return true;
            }
        }

        return false;
    }
}
