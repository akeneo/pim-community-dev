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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\SimpleAssociations;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindGroupLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\SimpleAssociationsValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class SimpleAssociationsGroupLabelSelectionHandler implements SelectionHandlerInterface
{
    private FindGroupLabelsInterface $findGroupLabels;

    public function __construct(FindGroupLabelsInterface $findGroupLabels)
    {
        $this->findGroupLabels = $findGroupLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof SimpleAssociationsGroupLabelSelection
            || !$value instanceof SimpleAssociationsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply simple associations group label selection on this entity');
        }

        $associatedGroupCodes = $value->getAssociatedGroupCodes();
        $associatedGroupsLabel = $this->findGroupLabels->byCodes(
            $associatedGroupCodes,
            $selection->getLocale()
        );

        $selectedData = \array_map(static fn ($associatedEntityCode) => $associatedGroupsLabel[$associatedEntityCode] ??
            \sprintf('[%s]', $associatedEntityCode), $associatedGroupCodes);

        return \implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof SimpleAssociationsGroupLabelSelection
            && $value instanceof SimpleAssociationsValue;
    }
}
