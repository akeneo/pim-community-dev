<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Exception\AlreadyExistingAxisValueCombinationException;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Contains the state of the unique axis values combination for an entity with family variant.
 * We use this state to deal with bulk update and validation.
 *
 * @author    Damien Carcel <damien.carcel@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueAxesCombinationSet
{
    /** @var array */
    private $uniqueAxesCombination;

    /**
     * Initializes the set.
     */
    public function __construct()
    {
        $this->uniqueAxesCombination = [];
    }

    /**
     * Resets the set.
     */
    public function reset(): void
    {
        $this->uniqueAxesCombination = [];
    }

    /**
     * Adds a new axis value combination. If it already exists, throw an
     * exception with the code/identifier of the entity that already contains
     * this combination.
     *
     * @param EntityWithFamilyVariantInterface $entity
     * @param string                           $axisValueCombination
     *
     * @throws AlreadyExistingAxisValueCombinationException
     */
    public function addCombination(EntityWithFamilyVariantInterface $entity, string $axisValueCombination): void
    {
        $familyVariantCode = $entity->getFamilyVariant()->getCode();
        $parentCode = $entity->getParent()->getCode();
        $identifier = $this->getEntityIdentifier($entity);

        if (isset($this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination])) {
            $cachedIdentifier = $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination];
            if ($cachedIdentifier !== $identifier) {
                if ($entity instanceof ProductInterface) {
                    throw new AlreadyExistingAxisValueCombinationException(
                        $cachedIdentifier,
                        sprintf(
                            'Variant product "%s" already have the "%s" combination of axis values.',
                            $cachedIdentifier,
                            $axisValueCombination
                        )
                    );
                }

                throw new AlreadyExistingAxisValueCombinationException(
                    $cachedIdentifier,
                    sprintf(
                        'Product model "%s" already have the "%s" combination of axis values.',
                        $cachedIdentifier,
                        $axisValueCombination
                    )
                );
            }
        }

        if (!isset($this->uniqueAxesCombination[$familyVariantCode])) {
            $this->uniqueAxesCombination[$familyVariantCode] = [];
        }

        if (!isset($this->uniqueAxesCombination[$familyVariantCode][$parentCode])) {
            $this->uniqueAxesCombination[$familyVariantCode][$parentCode] = [];
        }

        if (!isset($this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination])) {
            $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination] = $identifier;
        }
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return string
     */
    private function getEntityIdentifier(EntityWithFamilyVariantInterface $entity): string
    {
        if ($entity instanceof ProductInterface) {
            return $entity->getIdentifier();
        }

        return $entity->getCode();
    }
}
