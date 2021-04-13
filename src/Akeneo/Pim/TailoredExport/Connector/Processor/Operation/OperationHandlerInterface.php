<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;


interface OperationHandlerInterface
{
    public function handleOperation(array $operation, AttributeInterface $attribute, $value);
    public function supports(array $operation, AttributeInterface $attribute, $value);
}
