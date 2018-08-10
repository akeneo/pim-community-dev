<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityWithFamilyVariantRepositoryInterface
{
    /**
     * Find entities with the same parent than the given $entity.
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return EntityWithFamilyVariantInterface[]
     */
    public function findSiblings(EntityWithFamilyVariantInterface $entity): array;
}
