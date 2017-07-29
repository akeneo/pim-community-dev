<?php

namespace Pim\Component\Catalog\FamilyVariant;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;

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

        if ($entityWithFamilyVariant->isRootVariation()) {
            $attributes = $familyVariant->getCommonAttributes()->toArray();
        } else {
            $level = $entityWithFamilyVariant->getVariationLevel();
            $attributes = $familyVariant
                ->getVariantAttributeSet($level)
                ->getAttributes()
                ->toArray();
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

        if (null === $familyVariant || $entityWithFamilyVariant->isRootVariation()) {
            return [];
        }

        $level = $entityWithFamilyVariant->getVariationLevel();

        return $entityWithFamilyVariant
            ->getFamilyVariant()
            ->getVariantAttributeSet($level)
            ->getAxes()
            ->toArray();
    }
}
