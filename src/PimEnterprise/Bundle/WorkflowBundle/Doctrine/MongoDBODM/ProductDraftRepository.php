<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

/**
 * ProductDraft ODM repository
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftRepository extends DocumentRepository implements ProductDraftRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserProductDraft(ProductInterface $product, $username)
    {
        return $this
            ->createQueryBuilder('ProductDraft')
            ->field('author')->equals($username)
            ->field('product')->references($product)
            ->getQuery()->getSingleResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByProduct(ProductInterface $product)
    {
        return $this
            ->createQueryBuilder('ProductDraft')
            ->field('product')->references($product)
            ->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function createDatagridQueryBuilder(array $parameters = [])
    {
        $qb = $this->createQueryBuilder('p');

        if (isset($parameters['product'])) {
            $this->applyDatagridContext($qb, $parameters['product']);
        }

        return $qb;
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
