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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CategoriesReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class CategoriesReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof CategoriesReplacementOperation) {
            throw new UnexpectedValueException($operation, CategoriesReplacementOperation::class, self::class);
        }

        if (!$value instanceof StringValue && !$value instanceof ArrayValue) {
            throw new UnexpectedValueException($value, [StringValue::class, ArrayValue::class], self::class);
        }

        if ($value instanceof StringValue) {
            return $this->applyOperationOnStringValue($operation, $value);
        }

        return $this->applyOperationOnArrayValue($operation, $value);
    }

    private function applyOperationOnStringValue(CategoriesReplacementOperation $operation, StringValue $value): StringValue
    {
        $mappedValue = $operation->getMappedValue($value->getValue());

        if (null === $mappedValue) {
            return $value;
        }

        return new StringValue($mappedValue);
    }

    private function applyOperationOnArrayValue(CategoriesReplacementOperation $operation, ArrayValue $value): ArrayValue
    {
        $mappedValues = array_map(fn (string $value) => $this->applyOperationOnStringValue(
            $operation,
            new StringValue($value),
        )->getValue(), $value->getValue());

        return new ArrayValue($mappedValues);
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof CategoriesReplacementOperation;
    }
}
