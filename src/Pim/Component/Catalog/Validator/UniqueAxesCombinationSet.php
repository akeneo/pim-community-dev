<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Validator;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;

/**
 * Contains the state of the unique axes combination for an entity with family variant.
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
     * Returns TRUE if axes combination has been added, FALSE if it already
     * exists inside the set.
     *
     * @param EntityWithFamilyVariantInterface $entity
     * @param string                           $axesCombination
     *
     * @return bool
     */
    public function addCombination(EntityWithFamilyVariantInterface $entity, string $axesCombination): bool
    {
        $identifier = $this->getEntityId($entity);
        $familyVariantCode = $entity->getFamilyVariant()->getCode();

        if (isset($this->uniqueAxesCombination[$familyVariantCode][$axesCombination])) {
            $cachedIdentifier = $this->uniqueAxesCombination[$familyVariantCode][$axesCombination];
            if ($cachedIdentifier !== $identifier) {
                return false;
            }
        }

        if (!isset($this->uniqueAxesCombination[$familyVariantCode])) {
            $this->uniqueAxesCombination[$familyVariantCode] = [];
        }

        if (!isset($this->uniqueAxesCombination[$familyVariantCode][$axesCombination])) {
            $this->uniqueAxesCombination[$familyVariantCode][$axesCombination] = $identifier;
        }

        return true;
    }

    /**
     * spl_object_hash for new product and id when product exists
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return string
     */
    private function getEntityId(EntityWithFamilyVariantInterface $entity): string
    {
        return $entity->getId() ? (string) $entity->getId() : spl_object_hash($entity);
    }
}
