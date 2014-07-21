<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductCategoryRepository as BaseProductCategoryRepository;
use Doctrine\ODM\MongoDB\Query\Expr;
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
        if (count($grantedCategoryIds) > 0) {
            $qb->addOr($qb->expr()->field('categoryIds')->in($grantedCategoryIds));
        }
        $qb->addOr($qb->expr()->field('categoryIds')->size(0));
    }
}
