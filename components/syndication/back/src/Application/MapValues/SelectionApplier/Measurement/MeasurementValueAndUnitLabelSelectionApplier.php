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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Measurement;

use Akeneo\Platform\Syndication\Application\Common\Selection\Measurement\MeasurementValueAndUnitLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Domain\Query\FindUnitLabelInterface;

class MeasurementValueAndUnitLabelSelectionApplier implements MeasurementApplierInterface
{
    private FindUnitLabelInterface $findUnitLabels;

    public function __construct(FindUnitLabelInterface $findUnitLabels)
    {
        $this->findUnitLabels = $findUnitLabels;
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof MeasurementValueAndUnitLabelSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement value and unit label selection on this entity');
        }

        $unitCode = $value->getUnitCode();

        $unitTranslation = $this->findUnitLabels->byFamilyCodeAndUnitCode(
            $selection->getMeasurementFamilyCode(),
            $unitCode,
            $selection->getLocale()
        );

        $measurementValue = str_replace(self::DEFAULT_DECIMAL_SEPARATOR, $selection->getDecimalSeparator(), $value->getValue());
        $measurementUnitLabel = $unitTranslation ?? sprintf('[%s]', $unitCode);

        return sprintf('%s %s', $measurementValue, $measurementUnitLabel);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementValueAndUnitLabelSelection
            && $value instanceof MeasurementValue;
    }
}
