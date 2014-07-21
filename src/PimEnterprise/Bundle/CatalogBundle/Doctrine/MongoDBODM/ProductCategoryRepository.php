<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductCategoryRepository as BaseProductCategoryRepository;

use Doctrine\ODM\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

/**
 * Overriden product category repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductCategoryRepository extends BaseProductCategoryRepository
{
    /**
     * Add filter by all, means all products in granted categories and products not classified at all
     *
     * @param QueryBuilder $qb
     * @param integer[]    $grantedCategoryIds
     */
    public function addFilterByAll($qb, array $grantedCategoryIds)
    {
        $qb->addAnd(
            $qb->expr()->orX(
                $qb->expr()->field('categoryIds')->in($grantedCategoryIds),
                $qb->expr()->isNull('categoryIds')
            )
        );
    }
}
