<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToArrayOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertToArrayOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof ConvertToArrayOperation) {
            throw new \InvalidArgumentException(sprintf('Expecting %s, %s given', ConvertToArrayOperation::class, $operation::class));
        }

        if ($value instanceof ArrayValue) {
            return $value;
        }

        return new ArrayValue([$value->getValue()]);
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof ConvertToArrayOperation;
    }
}
