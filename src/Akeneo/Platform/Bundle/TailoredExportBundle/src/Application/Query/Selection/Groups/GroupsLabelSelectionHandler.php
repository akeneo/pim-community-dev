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

use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class GroupsLabelSelectionHandler implements SelectionHandlerInterface
{
    private GetGroupTranslations $getGroupTranslations;

    public function __construct(
        GetGroupTranslations $getGroupTranslations
    ) {
        $this->getGroupTranslations = $getGroupTranslations;
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
        $groupTranslations = $this->getGroupTranslations
            ->byGroupCodesAndLocale($groupCodes, $selection->getLocale());

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
