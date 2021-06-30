<?php

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Application\OperationApplier;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\OperationCollection;
use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue;

interface AttributeOperationApplierInterface
{
    public function applyOperation(OperationCollection $operationCollection, SourceValue $value, Attribute $attribute): SourceValue;
}
