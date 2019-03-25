<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Bundle\Sql\AttributeInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetFamilyAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAxesCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * This service checks if an attribute of an entity with family is editable.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CheckAttributeEditable
{
    /** @var GetFamilyAttributeCodes */
    private $getFamilyAttributeCodes;

    /** @var GetVariantAttributeSetAttributeCodes */
    private $getVariantAttributeSetAttributeCodes;

    /** @var GetVariantAttributeSetAxesCodes */
    private $getVariantAttributeSetAxesCodes;

    /**
     * @param GetFamilyAttributeCodes $getFamilyAttributeCodes
     * @param GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes
     * @param GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
     */
    public function __construct(
        GetFamilyAttributeCodes $getFamilyAttributeCodes,
        GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes,
        GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
    ) {
        $this->getFamilyAttributeCodes = $getFamilyAttributeCodes;
        $this->getVariantAttributeSetAttributeCodes = $getVariantAttributeSetAttributeCodes;
        $this->getVariantAttributeSetAxesCodes = $getVariantAttributeSetAxesCodes;
    }

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

        $familyAttributeCodes = $this->getFamilyAttributeCodes->execute($family->getCode());
        if (!in_array($attribute->getCode(), $familyAttributeCodes)) {
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
            $commonAttributeCodes = array_diff(
                $familyAttributeCodes,
                $this->getVariantAttributeSetAttributeCodes->execute($familyVariant->getCode(), 1),
                $this->getVariantAttributeSetAttributeCodes->execute($familyVariant->getCode(), 2)
            );

            return in_array($attribute->getCode(), $commonAttributeCodes);
        }

        return in_array($attribute->getCode(), $this->getAttributesForLevel($familyVariant->getCode(), $level));
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

    /**
     * @param string $familyVariantCode
     * @param int $level
     *
     * @return array
     */
    private function getAttributesForLevel(string $familyVariantCode, int $level): array
    {
        return array_diff(
            $this->getVariantAttributeSetAttributeCodes->execute($familyVariantCode, $level),
            $this->getVariantAttributeSetAxesCodes->execute($familyVariantCode, $level)
        );
    }
}
