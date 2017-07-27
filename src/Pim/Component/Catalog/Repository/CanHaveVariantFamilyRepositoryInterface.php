<?php

namespace Pim\Component\Catalog\Repository;

use Pim\Component\Catalog\Model\CanHaveFamilyVariantInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CanHaveVariantFamilyRepositoryInterface
{
    /**
     * Find entities with the same parent than the given $entity.
     *
     * @param CanHaveFamilyVariantInterface $entity
     *
     * @return array
     */
    public function findSiblings(CanHaveFamilyVariantInterface $entity): array;
}
