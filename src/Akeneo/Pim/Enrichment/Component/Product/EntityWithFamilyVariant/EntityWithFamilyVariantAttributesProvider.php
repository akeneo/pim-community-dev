<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Bundle\Sql\AttributeInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetFamilyAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAxesCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;

/**
 * Attributes and axes provider for EntityWithFamilyVariantInterface entities
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithFamilyVariantAttributesProvider
{
    /** @var LruArrayAttributeRepository */
    private $attributeRepository;

    /** @var GetFamilyAttributeCodes */
    private $getFamilyAttributeCodes;

    /** @var GetVariantAttributeSetAttributeCodes */
    private $getVariantAttributeSetAttributeCodes;

    /** @var GetVariantAttributeSetAxesCodes */
    private $getVariantAttributeSetAxesCodes;

    /**
     * @param LruArrayAttributeRepository $attributeRepository
     * @param GetFamilyAttributeCodes $getFamilyAttributeCodes
     * @param GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes
     * @param GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
     */
    public function __construct(
        LruArrayAttributeRepository $attributeRepository,
        GetFamilyAttributeCodes $getFamilyAttributeCodes,
        GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes,
        GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->getFamilyAttributeCodes = $getFamilyAttributeCodes;
        $this->getVariantAttributeSetAttributeCodes = $getVariantAttributeSetAttributeCodes;
        $this->getVariantAttributeSetAxesCodes = $getVariantAttributeSetAxesCodes;
    }

    /**
     * This method returns all attributes for the given $entityWithFamilyVariant, including axes.
     *
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(EntityWithFamilyVariantInterface $entityWithFamilyVariant): array
    {
        $familyVariant = $entityWithFamilyVariant->getFamilyVariant();

        if (null === $familyVariant) {
            return [];
        }

        $level = $entityWithFamilyVariant->getVariationLevel();
        if (EntityWithFamilyVariantInterface::ROOT_VARIATION_LEVEL === $level) {
            // FIXME: probably one query would be enough
            $attributeCodes = array_diff(
                $this->getFamilyAttributeCodes->execute($familyVariant->getFamily()->getCode()),
                $this->getVariantAttributeSetAttributeCodes->execute($familyVariant->getCode(), 1),
                $this->getVariantAttributeSetAttributeCodes->execute($familyVariant->getCode(), 2)
            );
        } else {
            $attributeCodes = $this->getVariantAttributeSetAttributeCodes->execute($familyVariant->getCode(), $level);
        }

        return $this->attributeRepository->findSeveralByIdentifiers($attributeCodes);
    }

    /**
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return AttributeInterface[]
     */
    public function getAxes(EntityWithFamilyVariantInterface $entityWithFamilyVariant): array
    {
        $familyVariant = $entityWithFamilyVariant->getFamilyVariant();

        $level = $entityWithFamilyVariant->getVariationLevel();
        if (null === $familyVariant || EntityWithFamilyVariantInterface::ROOT_VARIATION_LEVEL === $level) {
            return [];
        }

        return $this->attributeRepository->findSeveralByIdentifiers(
            $this->getVariantAttributeSetAxesCodes->execute($familyVariant->getCode(), $level)
        );
    }
}
