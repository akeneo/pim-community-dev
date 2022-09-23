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

namespace Akeneo\Platform\Syndication\Application\MapValues\OperationApplier;

use Akeneo\Platform\Syndication\Application\Common\Operation\MeasurementRoundingOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;

class MeasurementRoundingOperationApplier implements OperationApplierInterface
{
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

        $valueToRound = (float)$value->getValue();
        $precision = $operation->getPrecision();
        $roundingType = $operation->getType();

        $roundedValue = match ($roundingType) {
            'standard' => round($valueToRound, $precision),
            'round_up' => $this->ceil($valueToRound, $precision),
            'round_down' => $this->floor($valueToRound, $precision),
            default => throw new \RuntimeException(sprintf('Unsupported rounding type %s', $roundingType))
        };

        return new MeasurementValue((string)$roundedValue, $value->getUnitCode());
    }

    /**
     * @link https://gist.github.com/gh640/6d65226c6203f2cb0ebe42fbddca8ece
     */
    private function ceil(float $value, int $precision): float
    {
        $reg = $value + 0.5 / (10 ** $precision);

        return round($reg, $precision, $reg > 0 ? PHP_ROUND_HALF_DOWN : PHP_ROUND_HALF_UP);
    }

    /**
     * @link https://gist.github.com/gh640/6d65226c6203f2cb0ebe42fbddca8ece
     */
    private function floor(float $value, int $precision): float
    {
        $reg = $value - 0.5 / (10 ** $precision);

        return round($reg, $precision, $reg > 0 ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN);
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $value instanceof MeasurementValue && $operation instanceof MeasurementRoundingOperation;
    }
}
