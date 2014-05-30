<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use PimEnterprise\Bundle\WorkflowBundle\Model\Repository\ProposalRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;

/**
 * Proposal ORM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalRepository extends EntityRepository implements ProposalRepositoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        return $this
            ->createQueryBuilder('p');
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ORM\QueryBuilder
     */
    public function applyDatagridContext($qb, $productId)
    {
        $qb->innerJoin('p.product', 'product', 'WITH', $qb->expr()->eq('product.id', $productId));

        return $this;
    }

    /**
     * Find one open proposal
     *
     * @param integer $id
     *
     * @return null|Proposal
     */
    public function findOpen($id)
    {
        return $this->findOneBy(
            [
                'id'     => $id,
                'status' => Proposal::WAITING
            ]
        );
    }
}
