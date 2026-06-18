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
        $identifier = $entity->getIdentifier();

        // A product saved without an identifier (e.g. a variant product without an SKU)
        // cannot take part in the uniqueness check yet. Skip it so the "identifier is
        // required" validation can run instead of triggering a 500 error (issue #20486).
        if (null === $identifier) {
            return;
        }

        $loweredIdentifier = \mb_strtolower($identifier);
        $familyVariantCode = $entity->getFamilyVariant()->getCode();
        $parentCode = $entity->getParent()->getCode();
        $loweredAxisValueCombination = \mb_strtolower($axisValueCombination);

        if (isset($this->uniqueAxesCombination[$familyVariantCode][$parentCode][$loweredAxisValueCombination])) {
            $cachedIdentifier = $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$loweredAxisValueCombination];
            if ($cachedIdentifier !== $loweredIdentifier) {
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

        if (!isset($this->uniqueAxesCombination[$familyVariantCode][$parentCode][$loweredAxisValueCombination])) {
            $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$loweredAxisValueCombination] = $loweredIdentifier;
        }
    }
}
