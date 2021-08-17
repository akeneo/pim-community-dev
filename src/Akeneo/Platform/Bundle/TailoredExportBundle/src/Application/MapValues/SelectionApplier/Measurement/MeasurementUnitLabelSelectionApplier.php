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

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementUnitLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitLabelInterface;

class MeasurementUnitLabelSelectionApplier implements SelectionApplierInterface
{
    private FindUnitLabelInterface $findUnitLabels;

    public function __construct(FindUnitLabelInterface $findUnitLabels)
    {
        $this->findUnitLabels = $findUnitLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof MeasurementUnitLabelSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement unit label selection on this entity');
        }

        $unit = $value->getUnit();

        $unitTranslation = $this->findUnitLabels->byFamilyCodeAndUnitCode(
            $selection->getMeasurementFamily(),
            $unit,
            $selection->getLocale()
        );

        return $unitTranslation ?? sprintf('[%s]', $unit);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementUnitLabelSelection
            && $value instanceof MeasurementValue;
    }
}
