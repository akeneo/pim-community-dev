<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Exception\AlreadyExistingAxisValueCombinationException;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

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
     * exception with the code/[identifier or UUID] of the entity that already contains
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

        if (isset($this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination])) {
            $cachedIdentifierOrUuid = $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination];
            if ($entity instanceof ProductInterface) {
                $identifierOrUuidToCompare = $entity->getIdentifier() ?? $entity->getUuid()->toString();
                if ($cachedIdentifierOrUuid !== $identifierOrUuidToCompare) {
                    throw new AlreadyExistingAxisValueCombinationException(
                        $cachedIdentifierOrUuid,
                        sprintf(
                            'Variant product "%s" already have the "%s" combination of axis values.',
                            $cachedIdentifierOrUuid,
                            $axisValueCombination
                        )
                    );
                }
            } elseif ($cachedIdentifierOrUuid !== $entity->getIdentifier()) {
                throw new AlreadyExistingAxisValueCombinationException(
                    $cachedIdentifierOrUuid,
                    sprintf(
                        'Product model "%s" already have the "%s" combination of axis values.',
                        $cachedIdentifierOrUuid,
                        $axisValueCombination
                    )
                );
            }
        }

        if (!isset($this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination])) {
            if ($entity instanceof ProductInterface) {
                $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination] = $entity->getIdentifier() ?? $entity->getUuid()->toString();
            } else {
                $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination] = $entity->getIdentifier();
            }
        }
    }
}
