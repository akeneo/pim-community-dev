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
use Akeneo\Platform\TailoredExport\Domain\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;

class MeasurementUnitLabelSelectionHandler implements SelectionHandlerInterface
{
    private GetUnitTranslations $getUnitTranslations;

    public function __construct(GetUnitTranslations $getUnitTranslations)
    {
        $this->getUnitTranslations = $getUnitTranslations;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof MeasurementUnitLabelSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement selection on this entity');
        }

        $unit = $value->getUnit();

        if (null === $unit) {
            return '';
        }

        $unitTranslations = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale(
            $selection->getMetricFamily(),
            $selection->getLocale()
        );

        return $unitTranslations[$unit] ?? sprintf('[%s]', $unit);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementUnitLabelSelection
            && $value instanceof MeasurementValue;
    }
}
