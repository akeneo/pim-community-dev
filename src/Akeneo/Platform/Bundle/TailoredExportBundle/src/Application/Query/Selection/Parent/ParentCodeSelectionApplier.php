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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\Parent;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Parent\ParentCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ParentValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;

class ParentCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof ParentCodeSelection
            || !$value instanceof ParentValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Parent selection on this entity');
        }

        return $value->getParentCode();
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof ParentCodeSelection
            && $value instanceof ParentValue;
    }
}
