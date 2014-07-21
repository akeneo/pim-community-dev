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
        $expr = '';
        if (count($grantedCategoryIds) > 0) {
            $expr = 'filterCategory.id in(:filterCatIds) OR ';
            $qb->setParameter('filterCatIds', $grantedCategoryIds);
        }
        $qb->andWhere($expr.'filterCategory.id is null');
    }
}
