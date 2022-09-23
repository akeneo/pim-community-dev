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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\FamilyVariant;

use Akeneo\Platform\Syndication\Application\Common\Selection\FamilyVariant\FamilyVariantLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\FamilyVariantValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindFamilyVariantLabelInterface;

class FamilyVariantLabelSelectionApplier implements SelectionApplierInterface
{
    private FindFamilyVariantLabelInterface $findFamilyVariantLabel;

    public function __construct(FindFamilyVariantLabelInterface $findFamilyVariantLabel)
    {
        $this->findFamilyVariantLabel = $findFamilyVariantLabel;
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
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

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof FamilyVariantLabelSelection
            && $value instanceof FamilyVariantValue;
    }
}
