<?php

namespace Pim\Component\Catalog\ValuesFiller;

use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Values filler for Products.
 *
 * Their attributes come directly from the family, and values come directly from the entity.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductValuesFiller extends AbstractEntityWithFamilyValuesFiller
{
    /**
     * {@inheritdoc}
     */
    protected function checkEntityType(EntityWithFamilyInterface $entity): void
    {
        if (!$entity instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf('%s expected, %s given', ProductInterface::class, get_class($entity))
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedAttributes(EntityWithFamilyInterface $entity): array
    {
        $attributes = [];

        // TODO: remove this when optional attributes are gone
        $productAttributes = $entity->getAttributes();
        foreach ($productAttributes as $attribute) {
            $attributes[$attribute->getCode()] = $attribute;
        }

        $family = $entity->getFamily();
        if (null !== $family) {
            foreach ($family->getAttributes() as $attribute) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }
}
