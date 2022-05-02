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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\DecimalFormatterOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class DecimalFormatterOperationApplier implements OperationApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof DecimalFormatterOperation) {
            throw new \InvalidArgumentException(sprintf('Expecting %s, %s given', DecimalFormatterOperation::class, $operation::class));
        }

        if (!$value instanceof StringValue) {
            throw new \InvalidArgumentException(sprintf('Decimal Formatter Operation only supports String value, "%s" given', $value::class));
        }

        return new NumberValue(str_replace($operation->getDecimalSeparator(), static::DEFAULT_DECIMAL_SEPARATOR, $value->getValue()));
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof DecimalFormatterOperation;
    }
}
