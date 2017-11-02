<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamilyVariant;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModel;

/**
 * This service updates a collection of EntityWithFamilyVariantInterface to ensure their values
 * are matching the family variant structure, meaning only the value they should have depending on their level.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class KeepOnlyValuesForVariation
{
    /**
     * Update every EntityWithFamilyVariant values to only keep and set values allowed for their level.
     *
     * @param EntityWithFamilyVariantInterface[] $entitiesWithFamilyVariant
     */
    public function updateEntitiesWithFamilyVariant(array $entitiesWithFamilyVariant): void
    {
        foreach ($entitiesWithFamilyVariant as $entity) {
            $variationLevel = $entity->getVariationLevel();

            if (ProductModel::ROOT_VARIATION_LEVEL === $variationLevel) {
                $allowedAttributes = $entity->getFamilyVariant()->getCommonAttributes()->toArray();
            } else {
                $attributeSet = $entity->getFamilyVariant()->getVariantAttributeSet($variationLevel);
                $allowedAttributes = array_merge(
                    $attributeSet->getAttributes()->toArray(),
                    $attributeSet->getAxes()->toArray()
                );
            }

            $entityValues = $entity->getValues();
            foreach ($entityValues as $value) {
                $attribute = $value->getAttribute();

                if (!in_array($attribute, $allowedAttributes)) {
                    $entityValues->removeByAttribute($attribute);
                }
            }

            $entity->setValues($entityValues);
        }
    }
}
