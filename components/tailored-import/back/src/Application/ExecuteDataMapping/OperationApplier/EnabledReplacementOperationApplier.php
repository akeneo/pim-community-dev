<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\NoMappedValueFound;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\EnabledReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\BooleanValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class EnabledReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof EnabledReplacementOperation) {
            throw new UnexpectedValueException($operation, EnabledReplacementOperation::class, self::class);
        }

        if (!$value instanceof StringValue) {
            throw new UnexpectedValueException($value, StringValue::class, self::class);
        }

        if (!$operation->hasMappedValue($value->getValue())) {
            throw new NoMappedValueFound($value->getValue());
        }

        $mappedValue = $operation->getMappedValue($value->getValue());

        return new BooleanValue($mappedValue);
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof EnabledReplacementOperation;
    }
}
