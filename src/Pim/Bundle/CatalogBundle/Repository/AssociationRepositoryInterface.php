<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

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
     * Return the number of Associations for a specific association type
     *
     * @param AssociationTypeInterface $associationType
     *
     * @return mixed
     */
    public function countForAssociationType(AssociationTypeInterface $associationType);

    /**
     * Get the list of associations corresponding to the given owner IDs
     *
     * @param ProductInterface $product
     * @param array            $ownerIds
     *
     * @return \Pim\Bundle\CatalogBundle\Model\Association[]
     */
    public function findByProductAndOwnerIds(ProductInterface $product, array $ownerIds);
}
