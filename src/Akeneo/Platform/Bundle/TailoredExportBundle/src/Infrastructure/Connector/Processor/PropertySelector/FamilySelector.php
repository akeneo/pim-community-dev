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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FamilyValue;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;

class FamilySelector implements PropertySelectorInterface
{
    private GetFamilyTranslations $getFamilyTranslations;

    public function __construct(GetFamilyTranslations $getFamilyTranslations)
    {
        $this->getFamilyTranslations = $getFamilyTranslations;
    }

    public function applySelection(array $selectionConfiguration, SourceValueInterface $sourceValue): string
    {
        if (!$sourceValue instanceof FamilyValue) {
            throw new \LogicException('Cannot apply Family selection on this entity');
        }

        $familyCode = $sourceValue->getData();

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                return $familyCode;
            case SelectionTypes::LABEL:
                $familyTranslations = $this->getFamilyTranslations
                    ->byFamilyCodesAndLocale([$familyCode], $selectionConfiguration['locale']);

                return $familyTranslations[$familyCode] ?? sprintf('[%s]', $familyCode);
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }
    }

    public function supports(array $selectionConfiguration, SourceValueInterface $sourceValue): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::LABEL, SelectionTypes::CODE])
            && $sourceValue instanceof FamilyValue;
    }
}
