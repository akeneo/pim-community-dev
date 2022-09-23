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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Number;

use Akeneo\Platform\Syndication\Application\Common\Selection\Number\NumberSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NumberValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\NumberTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\StringTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class NumberSelectionApplier implements SelectionApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value)
    {
        if (!$selection instanceof NumberSelection || !$value instanceof NumberValue) {
            throw new \InvalidArgumentException('Cannot apply Number selection on this entity');
        }

        if ($target instanceof StringTarget) {
            // Doing an str_replace on a number will cast it to a string and then replace the default decimal separator (a dot)
            return str_replace(self::DEFAULT_DECIMAL_SEPARATOR, $selection->getDecimalSeparator(), $value->getData());
        }

        if ($target instanceof NumberTarget) {
            // https://stackoverflow.com/questions/16606364/cast-string-to-either-int-or-float
            /** @phpstan-ignore-next-line */
            return '' === $value->getData() ? null : $value->getData() + 0;
        }

        throw new \InvalidArgumentException('Cannot apply Number selection on this entity');
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof NumberSelection && $value instanceof NumberValue
            && ($target instanceof NumberTarget || $target instanceof StringTarget);
    }
}
