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

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementUnitCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;

class MeasurementUnitCodeSelectionApplier implements MeasurementApplierInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof MeasurementUnitCodeSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement unit code selection on this entity');
        }

        return $value->getUnitCode();
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementUnitCodeSelection
            && $value instanceof MeasurementValue;
    }
}
