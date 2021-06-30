<?php

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Application\OperationApplier;

use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\Operation;
use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue;

interface PropertyOperationApplierInterface
{
    public function applyOperation(Operation $operation, SourceValue $value): SourceValue;

    public function supports(Operation $operation, SourceValue $value);
}
