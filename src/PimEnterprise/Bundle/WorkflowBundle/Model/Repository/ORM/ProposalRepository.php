<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model\Repository\ORM;

use Doctrine\ORM\EntityRepository;

/**
 * Proposal ORM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p.id')
            ->addSelect('p.createdBy')
            ->addSelect('p.createdAt')
            ->addSelect('p.changes')
            ->where('p.product = :product')
            ->andWhere('p.status IS NULL');
    }

    /**
     * Find one open proposal
     *
     * @param int $id
     *
     * @return null|Proposal
     */
    public function findOpen($id)
    {
        return $this->findOneBy(
            [
                'id' => $id,
                'status' => null
            ]
        );
    }
}
