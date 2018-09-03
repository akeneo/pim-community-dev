<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * This service checks if an attribute of an entity with family is editable.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CheckAttributeEditable
{
    /**
     * @param EntityWithFamilyInterface $entity
     * @param AttributeInterface        $attribute
     *
     * @return bool
     * @throws \Exception
     */
    public function isEditable(EntityWithFamilyInterface $entity, AttributeInterface $attribute): bool
    {
        $family = $entity->getFamily();

        if (null === $family) {
            return true;
        }

        if (!$family->hasAttribute($attribute)) {
            return false;
        }

        if ($this->isNonVariantProduct($entity)) {
            return true;
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null === $familyVariant) {
            throw new \Exception('A family variant was expected for the entity.');
        }

        $level = $entity->getVariationLevel();
        if (0 === $level) {
            return $familyVariant->getCommonAttributes()->contains($attribute);
        }

        $attributeSet = $familyVariant->getVariantAttributeSet($level);
        if (null === $attributeSet) {
            throw new \Exception(
                sprintf(
                    'The variant attribute set of level "%d" was expected for the family variant "%s".',
                    $level,
                    $familyVariant->getCode()
                )
            );
        }

        return $attributeSet->hasAttribute($attribute);
    }

    /**
     * @param EntityWithFamilyInterface $entity
     *
     * @return bool
     */
    private function isNonVariantProduct(EntityWithFamilyInterface $entity): bool
    {
        if ($entity instanceof ProductModelInterface) {
            return false;
        }

        if ($entity instanceof ProductInterface) {
            return !$entity->isVariant();
        }

        return false;
    }
}
