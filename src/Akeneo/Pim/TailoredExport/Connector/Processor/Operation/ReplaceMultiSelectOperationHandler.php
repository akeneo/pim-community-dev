<?php

namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

class ReplaceMultiSelectOperationHandler implements OperationHandlerInterface
{
    public function supports(array $operation, Attribute $attribute, $value): bool
    {
        return $operation['type'] === 'replace' && $attribute->type() === AttributeTypes::OPTION_MULTI_SELECT && $value instanceof ValueInterface;
    }

    public function handleOperation(array $operation, Attribute $attribute, $value)
    {

        foreach ($value->getData() as $data) {

        }
    }
}
