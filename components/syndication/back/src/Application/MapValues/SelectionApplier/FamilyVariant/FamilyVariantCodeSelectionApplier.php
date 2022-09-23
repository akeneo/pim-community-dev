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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\FamilyVariant;

use Akeneo\Platform\Syndication\Application\Common\Selection\FamilyVariant\FamilyVariantCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\FamilyVariantValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class FamilyVariantCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof FamilyVariantCodeSelection
            || !$value instanceof FamilyVariantValue
        ) {
            throw new \InvalidArgumentException('Cannot apply FamilyVariant selection on this entity');
        }

        return $value->getFamilyVariantCode();
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof FamilyVariantCodeSelection
            && $value instanceof FamilyVariantValue;
    }
}
