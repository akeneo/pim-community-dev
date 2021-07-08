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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitLabelInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class MeasurementUnitLabelSelectionHandler implements SelectionHandlerInterface
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

        if (null === $unit) {
            return '';
        }

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
