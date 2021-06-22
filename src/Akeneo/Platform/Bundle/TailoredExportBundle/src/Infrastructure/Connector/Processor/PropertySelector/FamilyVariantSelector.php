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

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;

class FamilyVariantSelector implements PropertySelectorInterface
{
    private GetFamilyVariantTranslations $getFamilyVariantTranslations;

    public function __construct(
        GetFamilyVariantTranslations $getFamilyVariantTranslations
    ) {
        $this->getFamilyVariantTranslations = $getFamilyVariantTranslations;
    }

    public function applySelection(array $selectionConfiguration, $entity): string
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            throw new \LogicException('Cannot apply Family variant selection on this entity');
        }

        $familyVariant = $entity->getFamilyVariant();

        if (null === $familyVariant) {
            return '';
        }

        $familyVariantCode = $familyVariant->getCode();

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                return $familyVariantCode;
            case SelectionTypes::LABEL:
                $familyVariantTranslations = $this->getFamilyVariantTranslations
                    ->byFamilyVariantCodesAndLocale([$familyVariantCode], $selectionConfiguration['locale']);

                return $familyVariantTranslations[$familyVariantCode] ?? sprintf('[%s]', $familyVariantCode);
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }
    }

    public function supports(array $selectionConfiguration, string $propertyName): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::LABEL, SelectionTypes::CODE])
            && 'family_variant' === $propertyName;
    }
}
