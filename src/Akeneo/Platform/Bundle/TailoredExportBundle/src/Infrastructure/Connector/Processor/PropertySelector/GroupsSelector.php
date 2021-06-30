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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use Akeneo\Platform\TailoredExport\Domain\SourceValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;

class GroupsSelector implements PropertySelectorInterface
{
    private GetGroupTranslations $getGroupTranslations;

    public function __construct(
        GetGroupTranslations $getGroupTranslations
    ) {
        $this->getGroupTranslations = $getGroupTranslations;
    }

    public function applySelection(array $selectionConfiguration, SourceValue $sourceValue): string
    {
        if (!$sourceValue instanceof GroupsValue) {
            throw new \LogicException('Cannot apply group selection on this entity');
        }

        $groupCodes = $sourceValue->getData();

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                $selectedData = $groupCodes;
                break;
            case SelectionTypes::LABEL:
                $groupTranslations = $this->getGroupTranslations
                    ->byGroupCodesAndLocale($groupCodes, $selectionConfiguration['locale']);

                $selectedData = array_map(fn ($groupCode) => $groupTranslations[$groupCode] ??
                    sprintf('[%s]', $groupCode), $groupCodes);

                break;
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }

        return implode($selectionConfiguration['separator'], $selectedData);
    }

    public function supports(array $selectionConfiguration, SourceValue $sourceValue): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::LABEL, SelectionTypes::CODE])
            && $sourceValue instanceof GroupsValue;
    }
}
