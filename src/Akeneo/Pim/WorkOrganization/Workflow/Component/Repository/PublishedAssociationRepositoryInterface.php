<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Repository;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductAssociation;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;

/**
 * Published association repository contract
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface PublishedAssociationRepositoryInterface
{
    /**
     * Find a published association from a product association.
     * @param  string|int $ownerId
     */
    public function findOneByTypeAndOwner(int $associationTypeId, $ownerId): ?PublishedProductAssociation;

    /**
     * Remove a published product from all published associations.
     *
     * @param PublishedProductInterface $published
     * @param int                       $nbAssociationTypes
     */
    public function removePublishedProduct(PublishedProductInterface $published, $nbAssociationTypes = null);
}
