<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;

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
                $commonAttributes = $entity->getFamilyVariant()->getCommonAttributes();
                $attributeCodesToKeep = $this->getAttributeCodesToKeepForRoot($commonAttributes);
            } else {
                $variantAttributeSet = $entity->getFamilyVariant()->getVariantAttributeSet($variationLevel);
                $attributeCodesToKeep = $this->getAttributeCodesToKeepForDescendants($variantAttributeSet);
            }

            $entityValues = $entity->getValues();
            foreach ($entityValues as $value) {
                if (!in_array($value->getAttributeCode(), $attributeCodesToKeep)) {
                    $entityValues->removeByAttributeCode($value->getAttributeCode());
                }
            }

            $entity->setValues($entityValues);
        }
    }

    private function getAttributeCodesToKeepForRoot(CommonAttributeCollection $commonAttributes): array
    {
        $attributeCodesToKeep = [];
        foreach ($commonAttributes as $attribute) {
            $attributeCodesToKeep[] = $attribute->getCode();
        }

        return $attributeCodesToKeep;
    }

    private function getAttributeCodesToKeepForDescendants(VariantAttributeSetInterface $descendantAttributeSet): array
    {
        $attributesToKeep = array_merge(
            $descendantAttributeSet->getAttributes()->toArray(),
            $descendantAttributeSet->getAxes()->toArray()
        );

        $attributeCodesToKeep = [];
        foreach ($attributesToKeep as $attribute) {
            $attributeCodesToKeep[] = $attribute->getCode();
        }

        return $attributeCodesToKeep;
    }
}
