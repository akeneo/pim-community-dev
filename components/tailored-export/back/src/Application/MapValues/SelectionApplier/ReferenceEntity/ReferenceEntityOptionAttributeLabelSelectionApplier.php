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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityOptionAttributeLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindReferenceEntityOptionAttributeLabelsInterface;

class ReferenceEntityOptionAttributeLabelSelectionApplier implements SelectionApplierInterface
{
    public function __construct(
        private FindRecordsAttributeValueInterface $findRecordsAttributeValue,
        private FindReferenceEntityOptionAttributeLabelsInterface $findReferenceEntityOptionAttributeLabels,
    ) {
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$value instanceof ReferenceEntityValue || !$selection instanceof ReferenceEntityOptionAttributeLabelSelection) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity selection on this entity');
        }

        $optionLabels = $this->findReferenceEntityOptionAttributeLabels->find(
            $selection->getReferenceEntityAttributeIdentifier(),
        );

        $recordValues = array_change_key_case($this->findRecordsAttributeValue->find(
            $selection->getReferenceEntityCode(),
            [$value->getRecordCode()],
            $selection->getReferenceEntityAttributeIdentifier(),
            $selection->getChannel(),
            $selection->getLocale(),
        ));

        $optionCode = $recordValues[strtolower($value->getRecordCode())] ?? null;

        if (null === $optionCode) {
            return '';
        }

        return $optionLabels[$optionCode][$selection->getLabelLocale()] ?? sprintf('[%s]', $optionCode);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof ReferenceEntityOptionAttributeLabelSelection
            && $value instanceof ReferenceEntityValue;
    }
}
