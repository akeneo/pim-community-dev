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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\MultiSelect;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\MultiSelectValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class MultiSelectLabelSelectionHandler implements SelectionHandlerInterface
{
    private FindAttributeOptionLabelsInterface $getAttributeOptionLabels;

    public function __construct(
        FindAttributeOptionLabelsInterface $getAttributeOptionLabels
    ) {
        $this->getAttributeOptionLabels = $getAttributeOptionLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$value instanceof MultiSelectValue || !$selection instanceof MultiSelectLabelSelection) {
            throw new \InvalidArgumentException('Cannot apply Multi Select selection on this entity');
        }

        $attributeCode = $selection->getAttributeCode();
        $optionsCodes = $value->getOptionCodes();
        $locale = $selection->getLocale();

        $attributeOptionTranslations = $this->getAttributeOptionLabels->byAttributeCodeAndOptionCodes(
            $attributeCode, $optionsCodes, $locale
        );

        $selectedData = array_map(function ($optionCode) use ($attributeOptionTranslations, $attributeCode, $selection) {
            return $attributeOptionTranslations[$optionCode] ?? sprintf('[%s]', $optionCode);
        }, $optionsCodes);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof MultiSelectLabelSelection
            && $value instanceof MultiSelectValue;
    }
}
