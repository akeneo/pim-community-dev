<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ORM;

use Doctrine\ORM\QueryBuilder;

use Doctrine\ORM\EntityRepository;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ProposalRepositoryInterface;
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

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     */
    public function applyFilter($qb, $field, $operator, $value)
    {
        if ('IN' === $operator) {
            if (!empty($value)) {
                $fieldName = $this->getRootFieldName($qb, $field);
                $qb->andWhere($qb->expr()->in($fieldName, $value));
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     */
    public function applySorter($qb, $field, $direction)
    {
        $fieldName = $this->getRootFieldName($qb, $field);
        $qb->orderBy($fieldName, $direction);
    }

    /**
     * Build field name with root alias
     *
     * @param QueryBuilder $qb
     * @param string       $field
     *
     * @return string
     */
    protected function getRootFieldName(QueryBuilder $qb, $field)
    {
        return sprintf("%s.%s", current($qb->getRootAliases()), $field);
    }
}
