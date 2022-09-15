<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SimpleReferenceEntityReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class ReferenceEntitySingleLinkReplacementOperationApplier
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof SimpleReferenceEntityReplacementOperation) {
            throw new UnexpectedValueException($operation, SimpleReferenceEntityReplacementOperation::class, self::class);
        }

        if ($value instanceof InvalidValue) {
            return $value;
        }

        if (!$value instanceof StringValue) {
            throw new UnexpectedValueException($value, StringValue::class, self::class);
        }

        $mappedValue = $operation->getMappedValue($value->getValue());

        if (null === $mappedValue) {
            return $value;
        }

        return new StringValue($mappedValue);
    }
}
