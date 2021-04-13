<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

interface OperationHandlerInterface
{
    public function handleOperation(array $operation, Attribute $attribute, $value);
    public function supports(array $operation, Attribute $attribute, $value): bool;
}
