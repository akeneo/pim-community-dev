<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\MapValues\OperationApplier;

use Akeneo\Platform\TailoredExport\Application\Common\Operation\MeasurementRoundingOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\MeasurementConverterInterface;

class MeasurementRoundingOperationApplier implements OperationApplierInterface
{
    private MeasurementConverterInterface $measurementConverter;

    public function applyOperation(
        OperationInterface $operation,
        SourceValueInterface $value
    ): SourceValueInterface {
        if (
            !$operation instanceof MeasurementRoundingOperation
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement Rounding operation');
        }

        $roundedValue = $this->round(
            floatval($value->getValue()),
            $operation->getPrecision(),
            $operation->getType()
        );

        return new MeasurementValue((string)$roundedValue, $value->getUnitCode());
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $value instanceof MeasurementValue && $operation instanceof MeasurementRoundingOperation;
    }

    private function round(float $value, int $precision, $type): float
    {
        $roundedValue = match ($type) {
            'standard' => round($value, $precision),
            'up' => round($value, $precision, PHP_ROUND_HALF_UP),
            'down' => round($value, $precision, PHP_ROUND_HALF_DOWN),
        };

        return $roundedValue;
    }
}
