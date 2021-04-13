<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

class DefaultValueOperationHandler implements OperationHandlerInterface
{
    public function handleOperation(array $operation, Attribute $attribute, $value)
    {
        return $operation['value'];
    }

    public function supports(array $operation, Attribute $attribute, $value)
    {
        return 'default_value' === $operation['type'] && $value instanceof ValueInterface && (is_null($value) || !$value->hasData());
    }
}
