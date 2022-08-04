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

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class SplitOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof SplitOperation) {
            throw new UnexpectedValueException($operation, SplitOperation::class, self::class);
        }

        if ($value instanceof InvalidValue) {
            return $value;
        }

        if (!$value instanceof StringValue) {
            throw new UnexpectedValueException($value, StringValue::class, self::class);
        }

        return new ArrayValue(
            array_map('trim', explode($operation->getSeparator(), $value->getValue())),
        );
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof SplitOperation;
    }
}
