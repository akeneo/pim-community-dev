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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Measurement\MeasurementUnitCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;

class MeasurementUnitCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof MeasurementUnitCodeSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement unit code selection on this entity');
        }

        return $value->getUnit() ?? '';
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementUnitCodeSelection
            && $value instanceof MeasurementValue;
    }
}
