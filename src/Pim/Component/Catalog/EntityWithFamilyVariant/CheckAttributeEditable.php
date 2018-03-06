<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamilyVariant;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;

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

        if (!$family->hasAttributeCode($attribute->getCode())) {
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
        return !$entity instanceof VariantProductInterface && !$entity instanceof ProductModelInterface;
    }
}
