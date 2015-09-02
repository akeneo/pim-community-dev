<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\AbstractItemCategoryRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface as CatalogCategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;

/**
 * Product category repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryRepository extends AbstractItemCategoryRepository implements ProductCategoryRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getProductCountByTree(ProductInterface $product)
    {
        return $this->getItemCountByTree($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInCategory(CatalogCategoryInterface $category, QueryBuilder $categoryQb = null)
    {
        return $this->getItemsCountInCategory($category, $categoryQb);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsInCategory(CatalogCategoryInterface $category, QueryBuilder $categoryQb = null)
    {
        return $this->getItemIdsInCategory($category, $categoryQb);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByIds($qb, array $productIds, $include)
    {
        $rootAlias  = $qb->getRootAlias();
        if ($include) {
            $expression = $qb->expr()->in($rootAlias.'.id', $productIds);
            $qb->andWhere($expression);
        } else {
            $expression = $qb->expr()->notIn($rootAlias.'.id', $productIds);
            $qb->andWhere($expression);
        }
    }
}
