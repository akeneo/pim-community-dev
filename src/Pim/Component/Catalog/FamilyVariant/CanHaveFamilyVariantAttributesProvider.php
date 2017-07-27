<?php

namespace Pim\Component\Catalog\FamilyVariant;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CanHaveFamilyVariantInterface;

/**
 * Attributes and axes provider for CanHaveFamilyVariantInterface entities
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CanHaveFamilyVariantAttributesProvider
{
    /**
     * @param CanHaveFamilyVariantInterface $canHaveFamilyVariant
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(CanHaveFamilyVariantInterface $canHaveFamilyVariant): array
    {
        $familyVariant = $canHaveFamilyVariant->getFamilyVariant();

        if (null === $familyVariant) {
            return [];
        }

        if ($canHaveFamilyVariant->isRootVariation()) {
            $attributes = $familyVariant->getCommonAttributes()->toArray();
        } else {
            $level = $canHaveFamilyVariant->getVariationLevel();
            $attributes = $familyVariant
                ->getVariantAttributeSet($level)
                ->getAttributes()
                ->toArray();
        }

        return $attributes;
    }

    /**
     * @param CanHaveFamilyVariantInterface $canHaveFamilyVariant
     *
     * @return AttributeInterface[]
     */
    public function getAxes(CanHaveFamilyVariantInterface $canHaveFamilyVariant): array
    {
        $familyVariant = $canHaveFamilyVariant->getFamilyVariant();

        if (null === $familyVariant || $canHaveFamilyVariant->isRootVariation()) {
            return [];
        }

        $level = $canHaveFamilyVariant->getVariationLevel();

        return $canHaveFamilyVariant
            ->getFamilyVariant()
            ->getVariantAttributeSet($level)
            ->getAxes()
            ->toArray();
    }
}
