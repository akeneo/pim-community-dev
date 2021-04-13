<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class ReplaceTextOperationHandler implements OperationHandlerInterface
{
    public function handleOperation(array $operation, AttributeInterface $attribute, $value)
    {
        return $operation['mapping'][$value->getData()] ?? $value;
    }

    public function supports(array $operation, AttributeInterface $attribute, $value)
    {
        return 'replace' === $operation['type'] && $value instanceof ScalarValue && $value->hasData();
    }
}
