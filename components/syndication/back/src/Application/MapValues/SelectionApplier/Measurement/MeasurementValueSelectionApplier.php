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

use Akeneo\Platform\Syndication\Application\Common\Selection\Measurement\MeasurementValueSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\NumberTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\StringTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;

class MeasurementValueSelectionApplier implements MeasurementApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value)
    {
        if (
            !$selection instanceof MeasurementValueSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement value selection on this entity');
        }

        if ($target instanceof StringTarget) {
            // Doing an str_replace on a number will cast it to a string and then replace the default decimal separator (a dot)
            return str_replace(self::DEFAULT_DECIMAL_SEPARATOR, $selection->getDecimalSeparator(), $value->getValue());
        }

        if ($target instanceof NumberTarget) {
            /** @phpstan-ignore-next-line */
            return '' === $value->getValue() ? null : $value->getValue() + 0;
        }
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementValueSelection
            && $value instanceof MeasurementValue
            && ($target instanceof NumberTarget || $target instanceof StringTarget);
    }
}
