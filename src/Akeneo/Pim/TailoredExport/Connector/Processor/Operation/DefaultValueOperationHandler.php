<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class DefaultValueOperationHandler implements OperationHandlerInterface
{
    public function handleOperation(array $operation, AttributeInterface $attribute, $value)
    {
        return $operation['value'];
    }

    public function supports(array $operation, AttributeInterface $attribute, $value)
    {
        return 'default_value' === $operation['type'] && $value instanceof ValueInterface && !$value->hasData();
    }
}
