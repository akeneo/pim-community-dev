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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SimpleSelect;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleSelect\SimpleSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SimpleSelectValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface;

class SimpleSelectLabelSelectionApplier implements SelectionApplierInterface
{
    private FindAttributeOptionLabelsInterface $getAttributeOptionLabels;

    public function __construct(
        FindAttributeOptionLabelsInterface $getAttributeOptionLabels
    ) {
        $this->getAttributeOptionLabels = $getAttributeOptionLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$value instanceof SimpleSelectValue || !$selection instanceof SimpleSelectLabelSelection) {
            throw new \InvalidArgumentException('Cannot apply Simple Select selection on this entity');
        }

        $attributeCode = $selection->getAttributeCode();
        $optionCode = $value->getOptionCode();
        $attributeOptionTranslations = $this->getAttributeOptionLabels
            ->byAttributeCodeAndOptionCodes($attributeCode, [$optionCode], $selection->getLocale());

        return $attributeOptionTranslations[$optionCode] ?? sprintf('[%s]', $optionCode);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof SimpleSelectLabelSelection
            && $value instanceof SimpleSelectValue;
    }
}
