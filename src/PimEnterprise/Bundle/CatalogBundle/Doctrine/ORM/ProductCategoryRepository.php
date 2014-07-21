<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductCategoryRepository as BaseProductCategoryRepository;
use PimEnterprise\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;

/**
 * Overriden product category repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductCategoryRepository extends BaseProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function addFilterByAll($qb, array $grantedCategoryIds)
    {
        $rootAlias  = $qb->getRootAlias();
        $qb->leftJoin('p.categories', 'filterCategory');
        $qb->andWhere('filterCategory.id in(:filterCatIds) OR filterCategory.id is null');
        $qb->setParameter('filterCatIds', $grantedCategoryIds);
    }
}
