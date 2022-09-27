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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\FamilyReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class FamilyReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        return match (true) {
            !$operation instanceof FamilyReplacementOperation => throw new UnexpectedValueException($operation, FamilyReplacementOperation::class, self::class),
            $value instanceof InvalidValue => $value,
            $value instanceof StringValue => $this->replace($operation, $value),
            default => throw new UnexpectedValueException($value, StringValue::class, self::class)
        };
    }

    private function replace(FamilyReplacementOperation $operation, StringValue $value): StringValue
    {
        $mappedValue = $operation->getMappedValue($value->getValue());

        if (null === $mappedValue) {
            return $value;
        }

        return new StringValue($mappedValue);
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof FamilyReplacementOperation;
    }
}
