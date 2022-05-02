<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\BooleanValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof BooleanReplacementOperation) {
            throw new \InvalidArgumentException(sprintf('Expecting %s, "%s" given', BooleanReplacementOperation::class, $operation::class));
        }

        if (!$value instanceof StringValue) {
            throw new \InvalidArgumentException(sprintf('Boolean replacement Operation only supports String value, "%s" given', $value::class));
        }

        if (!array_key_exists($value->getValue(), $operation->getMapping())) {
            return new NullValue();
        }

        return new BooleanValue($operation->getMapping()[$value->getValue()]);
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof BooleanReplacementOperation;
    }
}
