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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\Groups;

use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Groups\GroupsLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindGroupLabelsInterface;

class GroupsLabelSelectionApplier implements SelectionApplierInterface
{
    private FindGroupLabelsInterface $findGroupLabels;

    public function __construct(FindGroupLabelsInterface $findGroupLabels)
    {
        $this->findGroupLabels = $findGroupLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof GroupsLabelSelection
            || !$value instanceof GroupsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Groups selection on this entity');
        }

        $groupCodes = $value->getGroupCodes();
        $groupTranslations = $this->findGroupLabels->byCodes($groupCodes, $selection->getLocale());

        $selectedData = array_map(fn ($groupCode) => $groupTranslations[$groupCode] ??
            sprintf('[%s]', $groupCode), $groupCodes);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof GroupsLabelSelection
            && $value instanceof GroupsValue;
    }
}
