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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\BooleanValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class BooleanReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        return match (true) {
            !$operation instanceof BooleanReplacementOperation => throw new UnexpectedValueException($operation, BooleanReplacementOperation::class, self::class),
            $value instanceof InvalidValue => $value,
            $value instanceof StringValue => $this->replace($operation, $value),
            default => throw new UnexpectedValueException($value, StringValue::class, self::class),
        };
    }

    private function replace(BooleanReplacementOperation $operation, StringValue $value): BooleanValue|InvalidValue
    {
        if ($operation->hasMappedValue($value->getValue())) {
            return new BooleanValue($operation->getMappedValue($value->getValue()));
        }

        return new InvalidValue(sprintf('There is no mapped value for this source value: "%s"', $value->getValue()));
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof BooleanReplacementOperation;
    }
}
