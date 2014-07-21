<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductCategoryRepository as BaseProductCategoryRepository;

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
        $rootAlias  = $qb->getRootAlias();
        $qb->leftJoin('p.categories', 'filterCategory');
        $qb->andWhere('filterCategory.id in(:filterCatIds) OR filterCategory.id is null');
        $qb->setParameter('filterCatIds', $grantedCategoryIds);
    }
}
