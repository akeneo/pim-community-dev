<?php

namespace Pim\Component\Catalog\Repository;

use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Interface for association repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AssociationRepositoryInterface
{
    /**
     * Get the list of associations corresponding to the given owner IDs
     *
     * @param ProductInterface $product
     * @param array            $ownerIds
     *
     * @return AssociationInterface[]
     */
    public function findByProductAndOwnerIds(ProductInterface $product, array $ownerIds);
}
