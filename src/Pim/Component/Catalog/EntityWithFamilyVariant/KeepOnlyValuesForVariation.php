<?php

namespace Pim\Component\Catalog\EntityWithFamilyVariant;

use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class KeepOnlyValuesForVariation
{
    /**
     * @param array $entities
     *
     * @return array
     */
    public function updateValues(array $entities): array
    {
        foreach ($entities as $entity) {
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

        return $entities;
    }
}
