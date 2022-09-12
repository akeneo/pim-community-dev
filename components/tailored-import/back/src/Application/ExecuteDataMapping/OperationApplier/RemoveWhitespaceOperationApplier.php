<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\RemoveWhitespaceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class RemoveWhitespaceOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof RemoveWhitespaceOperation) {
            throw new UnexpectedValueException($operation, RemoveWhitespaceOperation::class, self::class);
        }

        if ($value instanceof InvalidValue) {
            return $value;
        }

        if (!$value instanceof StringValue) {
            throw new UnexpectedValueException($value, StringValue::class, self::class);
        }

        foreach ($operation->getModes() as $mode) {
            $value = match ($mode) {
                RemoveWhitespaceOperation::MODE_CONSECUTIVE => new StringValue(preg_replace('/\s+/', ' ', $value->getValue())),
                RemoveWhitespaceOperation::MODE_TRIM => new StringValue(trim($value->getValue())),
                default => throw new \RuntimeException('Unsupported remove whitespace mode'),
            };
        }

        return $value;
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof RemoveWhitespaceOperation;
    }
}
