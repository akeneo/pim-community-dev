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

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementValueSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class MeasurementValueSelectionApplier implements SelectionApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof MeasurementValueSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement value selection on this entity');
        }

        return str_replace(static::DEFAULT_DECIMAL_SEPARATOR, $selection->getDecimalSeparator(), $value->getValue());
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementValueSelection
            && $value instanceof MeasurementValue;
    }
}
