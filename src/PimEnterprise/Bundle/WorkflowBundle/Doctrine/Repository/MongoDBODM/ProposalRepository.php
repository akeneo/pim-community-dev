<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ProposalRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;

/**
 * Proposal ODM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalRepository extends DocumentRepository implements ProposalRepositoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function createDatagridQueryBuilder()
    {
        return $this
            ->createQueryBuilder('p');
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ODM\MongoDB\Query\Builder $qb
     */
    public function applyDatagridContext($qb, $productId)
    {
        $qb->field('product.$id')->equals(new \MongoId($productId));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function applyFilter($qb, $field, $operator, $value)
    {
        if ('IN' === $operator) {
            if (!empty($value)) {
                $qb->field($field)->in($value);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function applySorter($qb, $field, $direction)
    {
        $qb->sort($field, $direction);
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
