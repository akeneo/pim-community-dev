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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\MultiSelect\MultiSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\MultiSelectValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
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
        $locale = $selection->getLocale();

        $attributeOptionTranslations = $this->getAttributeOptionLabels->byAttributeCodeAndOptionCodes(
            $attributeCode, $optionsCodes, $locale
        );

        $selectedData = array_map(static function ($optionCode) use ($attributeOptionTranslations) {
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
