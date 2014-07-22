<?php

namespace PimEnterprise\Bundle\CatalogBundle\Repository;

use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface as BaseProductCategoryRepositoryInterface;

/**
 * Add expected method to product category repository interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ProductCategoryRepositoryInterface extends BaseProductCategoryRepositoryInterface
{
    /**
     * Add filter by all, means all products in granted categories and products not classified at all
     *
     * @param QueryBuilder $qb
     * @param integer[]    $grantedCategoryIds
     */
    public function addFilterByAll($qb, array $grantedCategoryIds);
}
