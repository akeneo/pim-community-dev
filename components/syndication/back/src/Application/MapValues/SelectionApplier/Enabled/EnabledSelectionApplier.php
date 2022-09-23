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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Enabled;

use Akeneo\Platform\Syndication\Application\Common\Selection\Enabled\EnabledSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\BooleanTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\NumberTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\StringTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class EnabledSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value)
    {
        if (
            !$selection instanceof EnabledSelection
            || !$value instanceof EnabledValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Enabled selection on this entity');
        }

        if ($target instanceof StringTarget) {
            return $value->isEnabled() ? '1' : '0';
        }

        if ($target instanceof NumberTarget) {
            return $value->isEnabled() ? 1 : 0;
        }

        if ($target instanceof BooleanTarget) {
            return $value->isEnabled();
        }

        throw new \InvalidArgumentException('Cannot apply Enabled selection on this entity');
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof EnabledSelection
            && $value instanceof EnabledValue
            && ($target instanceof BooleanTarget || $target instanceof NumberTarget || $target instanceof StringTarget);
    }
}
