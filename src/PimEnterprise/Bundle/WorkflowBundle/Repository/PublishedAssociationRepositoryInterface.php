<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Pim\Component\Catalog\Model\AssociationTypeInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductAssociation;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Published association repository contract
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface PublishedAssociationRepositoryInterface
{
    /**
     * Find a published association from a product association.
     *
     * @param AssociationTypeInterface $type
     * @param int                      $ownerId
     *
     * @return PublishedProductAssociation|null
     */
    public function findOneByTypeAndOwner(AssociationTypeInterface $type, $ownerId);

    /**
     * Remove a published product from all published associations.
     *
     * @param PublishedProductInterface $published
     * @param int                       $nbAssociationTypes
     */
    public function removePublishedProduct(PublishedProductInterface $published, $nbAssociationTypes = null);
}
