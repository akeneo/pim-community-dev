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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\Groups;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class GroupsCodeSelectionHandler implements SelectionHandlerInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof GroupsCodeSelection
            || !$value instanceof GroupsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Groups selection on this entity');
        }

        return implode($selection->getSeparator(), $value->getGroupCodes());
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof GroupsCodeSelection
            && $value instanceof GroupsValue;
    }
}
