<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends FlexibleEntityRepository
{
    public function buildByScope($scope)
    {
        $qb = $this->findByWithAttributesQB();

        return $qb
            ->andWhere(
                $qb->expr()->eq('Entity.enabled', '?1')
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('Value.scope', '?2'),
                    $qb->expr()->isNull('Value.scope')
                )
            )
            ->setParameter(1, true)
            ->setParameter(2, $scope);
    }

    /**
     * Find products by existing family
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByExistingFamily()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->isNotNull('p.family'));

        return $qb->getQuery()->getResult();
    }
}
