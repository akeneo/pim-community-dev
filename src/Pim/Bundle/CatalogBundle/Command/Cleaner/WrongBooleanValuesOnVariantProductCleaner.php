<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command\Cleaner;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;

/**
 * Update the variant product to clean all the wrong boolean values.
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WrongBooleanValuesOnVariantProductCleaner
{
    /**
     * Return true if the product has been modified, false otherwise
     *
     * @param VariantProductInterface $variantProduct
     *
     * @return bool
     */
    public function updateProduct(VariantProductInterface $variantProduct): bool
    {
        $isModified = false;

        foreach ($variantProduct->getFamily()->getAttributes() as $attribute) {
            if ($this->isProductImpactedForAttribute($variantProduct, $attribute)) {
                $this->cleanProductForAttribute($variantProduct, $attribute);
                $isModified = true;
            }
        }

        return $isModified;
    }

    /**
     * @param VariantProductInterface $variantProduct
     * @param AttributeInterface      $attribute
     *
     * @return bool
     */
    private function isProductImpactedForAttribute(VariantProductInterface $variantProduct, AttributeInterface $attribute): bool
    {
        if ($attribute->getType() !== AttributeTypes::BOOLEAN) {
            return false;
        }

        $familyVariant = $variantProduct->getFamilyVariant();
        $attributeLevel = $familyVariant->getLevelForAttributeCode($attribute->getCode());
        $attributeIsOnLastLevel = $attributeLevel === $familyVariant->getNumberOfLevel();

        if ($attributeIsOnLastLevel) {
            return false;
        }

        return null !== $variantProduct->getValuesForVariation()->getByCodes($attribute->getCode());
    }

    /**
     * @param VariantProductInterface  $variantProduct
     * @param AttributeInterface       $attribute
     */
    private function cleanProductForAttribute(VariantProductInterface $variantProduct, AttributeInterface $attribute): void
    {
        $values = $variantProduct->getValues();
        $values->removeByAttribute($attribute);
        $variantProduct->setValues($values);
    }
}
