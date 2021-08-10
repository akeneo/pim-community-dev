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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\FamilyVariant;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\FamilyVariant\FamilyVariantLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindFamilyVariantLabelInterface;

class FamilyVariantLabelSelectionApplier implements SelectionApplierInterface
{
    private FindFamilyVariantLabelInterface $findFamilyVariantLabel;

    public function __construct(FindFamilyVariantLabelInterface $findFamilyVariantLabel)
    {
        $this->findFamilyVariantLabel = $findFamilyVariantLabel;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof FamilyVariantLabelSelection
            || !$value instanceof FamilyVariantValue
        ) {
            throw new \InvalidArgumentException('Cannot apply FamilyVariant selection on this entity');
        }

        $familyVariantCode = $value->getFamilyVariantCode();
        $familyVariantTranslation = $this->findFamilyVariantLabel->byCode($familyVariantCode, $selection->getLocale());

        return $familyVariantTranslation ?? sprintf('[%s]', $familyVariantCode);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof FamilyVariantLabelSelection
            && $value instanceof FamilyVariantValue;
    }
}
