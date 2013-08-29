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
    /**
     * @param string $scope
     *
     * @return QueryBuilder
     */
    public function buildByScope($scope)
    {
        $qb = $this->findByWithAttributesQB();
        $qb
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

        return $qb;
    }

    /**
     * @param string $scope
     *
     * @return QueryBuilder
     */
    public function buildByScopeAndCompleteness($scope)
    {
        $qb = $this->buildByScope($scope);
        $rootAlias = $qb->getRootAlias();
        $qb
            ->innerJoin($rootAlias .'.completenesses', 'pCompleteness', 'WITH', $qb->expr()->eq('pCompleteness.ratio', '100'))
            ->innerJoin('pCompleteness.channel', 'channel', 'WITH', $qb->expr()->eq('channel.code', $qb->expr()->literal($scope)));

        return $qb;
    }

    /**
     * Find products by existing family
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByExistingFamily()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where(
            $qb->expr()->isNotNull('p.family')
        );

        return $qb->getQuery()->getResult();
    }
}
