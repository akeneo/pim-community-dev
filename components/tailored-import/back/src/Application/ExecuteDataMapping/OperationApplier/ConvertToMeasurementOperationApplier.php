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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToMeasurementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\MeasurementValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class ConvertToMeasurementOperationApplier implements OperationApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        return match (true) {
            !$operation instanceof ConvertToMeasurementOperation => throw new UnexpectedValueException($operation, ConvertToMeasurementOperation::class, self::class),
            $value instanceof InvalidValue => $value,
            $value instanceof StringValue => $this->convertToMeasurement($operation, $value),
            default => throw new UnexpectedValueException($value, StringValue::class, self::class)
        };
    }

    private function convertToMeasurement(ConvertToMeasurementOperation $operation, StringValue $value): MeasurementValue|InvalidValue
    {
        $numberValue = str_replace(
            $operation->getDecimalSeparator(),
            self::DEFAULT_DECIMAL_SEPARATOR,
            $value->getValue(),
        );

        if (!is_numeric($numberValue)) {
            return new InvalidValue(sprintf(
                'Cannot convert "%s" to a number with separator "%s"',
                $value->getValue(),
                $operation->getDecimalSeparator(),
            ));
        }

        return new MeasurementValue($numberValue, $operation->getUnit());
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof ConvertToMeasurementOperation;
    }
}
