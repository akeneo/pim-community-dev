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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\RemoveWhitespaceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class RemoveWhitespaceOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        return match (true) {
            !$operation instanceof RemoveWhitespaceOperation => throw new UnexpectedValueException($operation, RemoveWhitespaceOperation::class, self::class),
            $value instanceof InvalidValue => $value,
            $value instanceof StringValue => $this->findModeAndApply($operation, $value),
            default => throw new UnexpectedValueException($value, StringValue::class, self::class),
        };
    }

    private function findModeAndApply(RemoveWhitespaceOperation $operation, StringValue $value): StringValue
    {
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
