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
     * @param \Doctrine\ORM\QueryBuilder
     */
    public function applyFilter($qb, $field, $operator, $value)
    {
        if ('IN' === $operator) {
            if (!empty($value)) {
                $fieldName = sprintf("%s.%s", $this->getRootAlias($qb), $field);
                $qb->andWhere($qb->expr()->in($fieldName, $value));
            }
        }
    }

    /**
     * Get the root alias
     *
     * @param QueryBuilder $qb
     *
     * @return string
     */
    protected function getRootAlias(QueryBuilder $qb)
    {
        return current($qb->getRootAliases());
    }
}
