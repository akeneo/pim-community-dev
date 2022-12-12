<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\ReferenceEntityCollection;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionOptionCollectionAttributeLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindReferenceEntityOptionAttributeLabelsInterface;

class ReferenceEntityCollectionOptionCollectionAttributeLabelSelectionApplier implements SelectionApplierInterface
{
    public function __construct(
        private FindRecordsAttributeValueInterface $findRecordsAttributeValue,
        private FindReferenceEntityOptionAttributeLabelsInterface $findReferenceEntityOptionAttributeLabels,
    ) {
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$value instanceof ReferenceEntityCollectionValue || !$selection instanceof ReferenceEntityCollectionOptionCollectionAttributeLabelSelection) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity Collection selection on this entity');
        }

        $optionLabels = array_change_key_case($this->findReferenceEntityOptionAttributeLabels->find(
            $selection->getReferenceEntityAttributeIdentifier(),
        ));

        $recordCodes = $value->getRecordCodes();
        $recordValues = array_change_key_case($this->findRecordsAttributeValue->find(
            $selection->getReferenceEntityCode(),
            $recordCodes,
            $selection->getReferenceEntityAttributeIdentifier(),
            $selection->getChannel(),
            $selection->getLocale(),
        ));

        $selectedData = array_map(static function (string $recordCode) use ($value, $recordValues, $optionLabels, $selection) {
            if ($value->hasMappedValue($recordCode)) {
                return $value->getMappedValue($recordCode);
            }

            $optionCodes = $recordValues[strtolower($recordCode)] ?? [];

            $optionLabels = array_map(
                static fn (string $optionCode) => $optionLabels[strtolower($optionCode)][$selection->getLabelLocale()] ?? sprintf('[%s]', $optionCode),
                $optionCodes,
            );

            return implode($selection->getOptionSeparator(), $optionLabels);
        }, $recordCodes);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof ReferenceEntityCollectionOptionCollectionAttributeLabelSelection
            && $value instanceof ReferenceEntityCollectionValue;
    }
}
