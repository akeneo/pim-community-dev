<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductAssociation;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Published association repository contract
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface PublishedAssociationRepositoryInterface
{
    /**
     * Find a published association from a product association.
     *
     * @param AssociationType $type
     * @param int             $ownerId
     *
     * @return PublishedProductAssociation|null
     */
    public function findOneByTypeAndOwner(AssociationType $type, $ownerId);

    /**
     * Remove a published product from all published associations.
     *
     * @param PublishedProductInterface $published
     * @param int                       $nbAssociationTypes
     */
    public function removePublishedProduct(PublishedProductInterface $published, $nbAssociationTypes = null);
}
