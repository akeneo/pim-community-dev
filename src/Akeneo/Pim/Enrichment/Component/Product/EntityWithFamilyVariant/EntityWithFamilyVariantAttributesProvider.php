<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Attributes and axes provider for EntityWithFamilyVariantInterface entities
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithFamilyVariantAttributesProvider
{
    /**
     * This method returns all attributes for the given $entityWithFamilyVariant, including axes.
     *
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(EntityWithFamilyVariantInterface $entityWithFamilyVariant): array
    {
        $familyVariant = $entityWithFamilyVariant->getFamilyVariant();

        if (null === $familyVariant) {
            return [];
        }

        $level = $entityWithFamilyVariant->getVariationLevel();
        if (EntityWithFamilyVariantInterface::ROOT_VARIATION_LEVEL === $level) {
            $attributes = $familyVariant->getCommonAttributes()->toArray();
        } else {
            $variantAttributeSet = $familyVariant->getVariantAttributeSet($level);
            if (null === $variantAttributeSet) {
                return [];
            }

            $attributes = $variantAttributeSet->getAttributes()->toArray();
        }

        return $attributes;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return AttributeInterface[]
     */
    public function getAxes(EntityWithFamilyVariantInterface $entityWithFamilyVariant): array
    {
        $familyVariant = $entityWithFamilyVariant->getFamilyVariant();

        $level = $entityWithFamilyVariant->getVariationLevel();
        if (null === $familyVariant || EntityWithFamilyVariantInterface::ROOT_VARIATION_LEVEL === $level) {
            return [];
        }

        return $entityWithFamilyVariant
            ->getFamilyVariant()
            ->getVariantAttributeSet($level)
            ->getAxes()
            ->toArray();
    }
}
