<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\FamilyVariant;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;

/**
 * Automatically sets identifier attribute and attributes with unique value in
 * the variant attribute set corresponding to the variant product.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddUniqueAttributesToVariantProductAttributeSet
{
    /**
     * @param FamilyVariantInterface $familyVariant
     */
    public function addUniqueAttributesToFamilyVariant(FamilyVariantInterface $familyVariant)
    {
        $familyUniqueAttributes = $this->getFamilyUniqueAttributes($familyVariant->getFamily());

        $familyVariantAttributeCodes = $familyVariant->getAttributes()->map(
            function (AttributeInterface $attribute) {
                return $attribute->getCode();
            }
        )->toArray();

        $variantProductAttributeSet = $familyVariant->getVariantAttributeSet($familyVariant->getNumberOfLevel());

        foreach ($familyUniqueAttributes as $uniqueAttribute) {
            if (!in_array($uniqueAttribute->getCode(), $familyVariantAttributeCodes)) {
                $variantProductAttributeSet->addAttribute($uniqueAttribute);
            }
        }
    }

    /**
     * @param FamilyInterface $family
     *
     * @return AttributeInterface[]
     */
    private function getFamilyUniqueAttributes(FamilyInterface $family): array
    {
        $uniqueAttributes = [];
        foreach ($family->getAttributes() as $attribute) {
            if (AttributeTypes::IDENTIFIER === $attribute->getType() || $attribute->isUnique()) {
                $uniqueAttributes[] = $attribute;
            }
        }

        return $uniqueAttributes;
    }
}
