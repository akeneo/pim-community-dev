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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SimpleAssociations;

use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SimpleAssociations\SimpleAssociationsGroupsLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SimpleAssociationsValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindGroupLabelsInterface;

class SimpleAssociationsGroupsLabelSelectionApplier implements SelectionApplierInterface
{
    private FindGroupLabelsInterface $findGroupLabels;

    public function __construct(FindGroupLabelsInterface $findGroupLabels)
    {
        $this->findGroupLabels = $findGroupLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof SimpleAssociationsGroupsLabelSelection
            || !$value instanceof SimpleAssociationsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply simple associations groups label selection on this entity');
        }

        $associatedGroupCodes = $value->getAssociatedGroupCodes();
        $associatedGroupsLabel = $this->findGroupLabels->byCodes(
            $associatedGroupCodes,
            $selection->getLocale()
        );

        $selectedData = $this->formatLabels($associatedGroupsLabel, $associatedGroupCodes);

        return \implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof SimpleAssociationsGroupsLabelSelection
            && $value instanceof SimpleAssociationsValue;
    }

    private function formatLabels(array $associatedGroupsLabel, array $associatedGroupCodes): array
    {
        return \array_map(
            static fn ($associatedEntityCode) => $associatedGroupsLabel[$associatedEntityCode] ?? \sprintf('[%s]', $associatedEntityCode),
            $associatedGroupCodes
        );
    }
}
