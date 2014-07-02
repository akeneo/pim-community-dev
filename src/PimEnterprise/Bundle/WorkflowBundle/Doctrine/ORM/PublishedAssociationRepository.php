<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface;

/**
 * Published association repository
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedAssociationRepository extends EntityRepository implements PublishedAssociationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByTypeAndOwner(AssociationType $type, $ownerId)
    {
        return $this->findOneBy(
            [
                'owner' => $ownerId,
                'associationType' => $type,
            ]
        );
    }
}
