<?php

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Application\OperationApplier;

use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\Operation;
use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\ReplacementOperation;
use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue\StringValue;
use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue;

class EnabledReplacementApplier implements PropertyOperationApplierInterface
{
    public function applyOperation(Operation $operation, SourceValue $value): SourceValue
    {
        if (!$operation instanceof ReplacementOperation) {
            throw new \Exception('NOOOOOOO');
        }

        if (!$value instanceof BooleanValue) {
            throw new \Exception('NOOOOOOO');
        }

        $data = $value->getData();
        if ($operation->hasMappedValue($data)) {
            $mappedValue = $operation->getMappedValue($data);

            return new StringValue($mappedValue);
        }

        return $value;
    }

    public function supports(Operation $operation, SourceValue $value): bool
    {
        return $value instanceof BooleanValue && $operation instanceof ReplacementOperation;
    }
}
