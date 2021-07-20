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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FamilyValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class FamilyLabelSelectionHandler implements SelectionHandlerInterface
{
    private GetFamilyTranslations $getFamilyTranslations;

    public function __construct(GetFamilyTranslations $getFamilyTranslations)
    {
        $this->getFamilyTranslations = $getFamilyTranslations;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof FamilyLabelSelection
            || !$value instanceof FamilyValue) {
            throw new \InvalidArgumentException('Cannot apply Family selection on this entity');
        }

        $familyCode = $value->getFamilyCode();
        $familyTranslations = $this->getFamilyTranslations
            ->byFamilyCodesAndLocale([$familyCode], $selection->getLocale());

        return $familyTranslations[$familyCode] ?? sprintf('[%s]', $familyCode);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof FamilyLabelSelection
            && $value instanceof FamilyValue;
    }
}
