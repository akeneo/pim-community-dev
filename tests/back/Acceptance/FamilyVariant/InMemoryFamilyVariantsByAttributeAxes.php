<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\FamilyVariant;

use Akeneo\Pim\Structure\Component\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;

class InMemoryFamilyVariantsByAttributeAxes implements FamilyVariantsByAttributeAxesInterface
{
    /** @var InMemoryFamilyVariantRepository */
    private $familyVariantRepository;

    public function __construct(
        InMemoryFamilyVariantRepository $familyVariantRepository
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
    }

    public function findIdentifiers(array $attributeAxesCodes): array
    {
        $codes = [];

        /** @var FamilyVariant[] $familyVariants */
        $familyVariants = $this->familyVariantRepository->findAll();

        foreach ($familyVariants as $familyVariant) {
            /** @var VariantAttributeSet $variantAttributeSet */
            foreach ($familyVariant->getVariantAttributeSets() as $variantAttributeSet) {
                /** @var AttributeInterface $axe */
                foreach ($variantAttributeSet->getAxes() as $axe) {
                    if (in_array($axe->getCode(), $attributeAxesCodes)) {
                        $codes[] = $familyVariant->getCode();
                    }
                }
            }
        }

        return $codes;
    }
}
