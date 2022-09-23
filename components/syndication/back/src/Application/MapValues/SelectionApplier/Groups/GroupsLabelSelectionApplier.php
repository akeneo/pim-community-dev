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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Groups;

use Akeneo\Platform\Syndication\Application\Common\Selection\Groups\GroupsLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\GroupsValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindGroupLabelsInterface;

class GroupsLabelSelectionApplier implements SelectionApplierInterface
{
    private FindGroupLabelsInterface $findGroupLabels;

    public function __construct(FindGroupLabelsInterface $findGroupLabels)
    {
        $this->findGroupLabels = $findGroupLabels;
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof GroupsLabelSelection
            || !$value instanceof GroupsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Groups selection on this entity');
        }

        $groupCodes = $value->getGroupCodes();
        $groupTranslations = $this->findGroupLabels->byCodes($groupCodes, $selection->getLocale());

        $selectedData = array_map(static fn ($groupCode) => $groupTranslations[$groupCode] ??
            sprintf('[%s]', $groupCode), $groupCodes);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof GroupsLabelSelection
            && $value instanceof GroupsValue;
    }
}
