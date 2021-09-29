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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\MultiSelect;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\MultiSelect\MultiSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MultiSelectValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface;

class MultiSelectLabelSelectionApplier implements SelectionApplierInterface
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
        $mappedReplacementValues = $value->getMappedReplacementValues();
        $locale = $selection->getLocale();

        $attributeOptionTranslations = $this->getAttributeOptionLabels->byAttributeCodeAndOptionCodes(
            $attributeCode,
            $optionsCodes,
            $locale
        );

        $selectedData = array_map(static function ($optionCode) use ($attributeOptionTranslations, $mappedReplacementValues) {
            if (array_key_exists($optionCode, $mappedReplacementValues)) {
                return $mappedReplacementValues[$optionCode];
            }

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
