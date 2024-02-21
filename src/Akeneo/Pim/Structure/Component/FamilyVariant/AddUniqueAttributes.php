<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\FamilyVariant;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;

/**
 * Automatically sets identifier attribute and attributes with unique value in
 * the bottom attribute set.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddUniqueAttributes
{
    /**
     * @param FamilyVariantInterface $familyVariant
     */
    public function addToFamilyVariant(FamilyVariantInterface $familyVariant)
    {
        $familyUniqueAttributes = $this->getFamilyUniqueAttributes($familyVariant->getFamily());

        $familyVariantAttributeCodes = $familyVariant->getAttributes()->map(
            function (AttributeInterface $attribute) {
                return $attribute->getCode();
            }
        )->toArray();

        $bottomAttributeSet = $familyVariant->getVariantAttributeSet($familyVariant->getNumberOfLevel());

        foreach ($familyUniqueAttributes as $uniqueAttribute) {
            if (!in_array($uniqueAttribute->getCode(), $familyVariantAttributeCodes)) {
                $bottomAttributeSet->addAttribute($uniqueAttribute);
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
