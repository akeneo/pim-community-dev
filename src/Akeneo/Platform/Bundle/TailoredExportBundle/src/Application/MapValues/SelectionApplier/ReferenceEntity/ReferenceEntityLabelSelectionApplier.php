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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\ReferenceEntity\ReferenceEntityLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordLabelsInterface;

class ReferenceEntityLabelSelectionApplier implements SelectionApplierInterface
{
    private FindRecordLabelsInterface $findRecordLabels;

    public function __construct(FindRecordLabelsInterface $findRecordLabels)
    {
        $this->findRecordLabels = $findRecordLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$value instanceof ReferenceEntityValue || !$selection instanceof ReferenceEntityLabelSelection) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity selection on this entity');
        }

        $recordCode = $value->getRecordCode();
        $referenceEntityCode = $selection->getReferenceEntityCode();
        $recordTranslations = $this->findRecordLabels->byReferenceEntityCodeAndRecordCodes(
            $referenceEntityCode,
            [$recordCode],
            $selection->getLocale()
        );

        return $recordTranslations[$recordCode] ?? sprintf('[%s]', $recordCode);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof ReferenceEntityLabelSelection
            && $value instanceof ReferenceEntityValue;
    }
}
