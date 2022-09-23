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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Categories;

use Akeneo\Platform\Syndication\Application\Common\Selection\Categories\CategoriesCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\CategoriesValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class CategoriesCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof CategoriesCodeSelection
            || !$value instanceof CategoriesValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Categories selection on this entity');
        }

        $categoryCodes = $value->getCategoryCodes();

        return implode($selection->getSeparator(), $categoryCodes);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof CategoriesCodeSelection
            && $value instanceof CategoriesValue;
    }
}
