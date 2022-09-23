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

use Akeneo\Platform\Syndication\Application\Common\Selection\Measurement\MeasurementSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;

class MeasurementSelectionApplier implements MeasurementApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value)
    {
        if (
            !$selection instanceof MeasurementSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement selection on this entity');
        }

        return [
            'value' => $value->getValue(),
            'unit' => $value->getUnitCode(),
        ];
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementSelection
            && $value instanceof MeasurementValue;
    }
}
