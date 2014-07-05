<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Proposition ODM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionRepository extends DocumentRepository implements PropositionRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserProposition(ProductInterface $product, $username)
    {
        return $this
            ->createQueryBuilder('Proposition')
            ->field('author')->equals($username)
            ->field('product')->references($product)
            ->getQuery()->getSingleResult();
    }

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
}
