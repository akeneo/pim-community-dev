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

        if (isset($this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination])) {
            $cachedIdentifierOrUuid = $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination];
            if ($entity instanceof ProductInterface && $cachedIdentifierOrUuid['uuid'] !== $entity->getUuid()->toString()) {
                throw new AlreadyExistingAxisValueCombinationException(
                    $cachedIdentifierOrUuid['identifier'] ?? $cachedIdentifierOrUuid['uuid'],
                    sprintf(
                        'Variant product "%s" already have the "%s" combination of axis values.',
                        $cachedIdentifierOrUuid['identifier'] ?? $cachedIdentifierOrUuid['uuid'],
                        $axisValueCombination
                    )
                );
            } elseif ($entity instanceof ProductModelInterface && $cachedIdentifierOrUuid['identifier'] !== $entity->getIdentifier()) {
                throw new AlreadyExistingAxisValueCombinationException(
                    $cachedIdentifierOrUuid['identifier'],
                    sprintf(
                        'Product model "%s" already have the "%s" combination of axis values.',
                        $cachedIdentifierOrUuid['identifier'],
                        $axisValueCombination
                    )
                );
            }
        }

        if (!isset($this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination])) {
            $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination]['identifier'] = $entity->getIdentifier();
            if ($entity instanceof ProductInterface) {
                $this->uniqueAxesCombination[$familyVariantCode][$parentCode][$axisValueCombination]['uuid'] = $entity->getUuid()->toString();
            }
        }
    }
}
