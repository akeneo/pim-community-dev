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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SimpleSelect;

use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\Selection\SimpleSelect\SimpleSelectCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SimpleSelectValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class SimpleSelectCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof SimpleSelectCodeSelection
            || !$value instanceof SimpleSelectValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Simple Select selection on this entity');
        }

        return $value->getOptionCode();
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof SimpleSelectCodeSelection
            && $value instanceof SimpleSelectValue;
    }
}
