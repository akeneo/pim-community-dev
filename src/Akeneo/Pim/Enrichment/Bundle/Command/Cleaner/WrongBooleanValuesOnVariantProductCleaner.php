<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\Cleaner;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Pim\Component\Catalog\AttributeTypes;

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
     * @param ProductInterface $variantProduct
     *
     * @return bool
     */
    public function cleanProduct(ProductInterface $variantProduct): bool
    {
        if (!$variantProduct->isVariant()) {
            return false;
        }

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
     * @param ProductInterface   $variantProduct
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    private function isProductImpactedForAttribute(ProductInterface $variantProduct, AttributeInterface $attribute): bool
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
     * @param ProductInterface   $variantProduct
     * @param AttributeInterface $attribute
     */
    private function cleanProductForAttribute(ProductInterface $variantProduct, AttributeInterface $attribute): void
    {
        $values = $variantProduct->getValues();
        $values->removeByAttribute($attribute);
        $variantProduct->setValues($values);
    }
}
