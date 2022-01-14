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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Boolean;

use Akeneo\Platform\Syndication\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\BooleanTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\NumberTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\StringTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class BooleanSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value)
    {
        if (
            !$selection instanceof BooleanSelection
            || !$value instanceof BooleanValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Boolean selection on this entity');
        }

        if ($target instanceof StringTarget) {
            return $value->getData() ? '1' : '0';
        }

        if ($target instanceof NumberTarget) {
            return $value->getData() ? 1 : 0;
        }

        if ($target instanceof BooleanTarget) {
            return $value->getData();
        }

        throw new \InvalidArgumentException('Cannot apply Boolean selection on this entity');
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof BooleanSelection
            && $value instanceof BooleanValue
            && ($target instanceof BooleanTarget || $target instanceof NumberTarget || $target instanceof StringTarget);
    }
}
