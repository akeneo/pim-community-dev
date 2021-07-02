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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\FamilyVariant;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class FamilyVariantCodeSelectionHandler implements SelectionHandlerInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof FamilyVariantCodeSelection
            || !$value instanceof FamilyVariantValue
        ) {
            throw new \InvalidArgumentException('Cannot apply FamilyVariant selection on this entity');
        }

        return $value->getData();
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof FamilyVariantCodeSelection
            && $value instanceof FamilyVariantValue;
    }
}
