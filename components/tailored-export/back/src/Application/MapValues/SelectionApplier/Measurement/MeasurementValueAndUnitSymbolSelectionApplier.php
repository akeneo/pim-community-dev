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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\Measurement;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementValueAndUnitSymbolSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitSymbolInterface;

class MeasurementValueAndUnitSymbolSelectionApplier implements MeasurementApplierInterface
{
    public function __construct(
        private FindUnitSymbolInterface $findUnitSymbol,
    ) {
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof MeasurementValueAndUnitSymbolSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement value and unit symbol selection on this entity');
        }

        $measurementValue = str_replace(self::DEFAULT_DECIMAL_SEPARATOR, $selection->getDecimalSeparator(), $value->getValue());
        $measurementUnitSymbol = $this->findUnitSymbol->byFamilyCodeAndUnitCode(
            $selection->getMeasurementFamilyCode(),
            $value->getUnitCode(),
        );

        return sprintf('%s %s', $measurementValue, $measurementUnitSymbol);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementValueAndUnitSymbolSelection
            && $value instanceof MeasurementValue;
    }
}
