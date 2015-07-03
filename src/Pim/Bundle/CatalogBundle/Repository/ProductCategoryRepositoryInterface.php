<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product category repository interface
 *
 * TODO: In 1.5 this interface should extend ItemCategoryRepositoryInterface and
 *       CategoryFilterableRepositoryInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductCategoryRepositoryInterface
{
    /**
     * Return the number of times the product is present in each tree
     *
     * @param ProductInterface $product The product to look for in the trees
     *
     * @return array Each row of the array has the format:'tree'=>treeObject, 'productCount'=>integer
     *
     * @deprecated Will be removed in 1.5. Please use ItemCategoryRepositoryInterface.
     */
    public function getProductCountByTree(ProductInterface $product);

    /**
     * Get product ids linked to a category or its children.
     * You can define if you just want to get the property of the actual node or with its children with the direct
     * parameter
     *
     * @param CategoryInterface $category   the requested node
     * @param QueryBuilder      $categoryQb category query buider
     *
     * @return array
     *
     * @deprecated Will be removed in 1.5. Please use ItemCategoryRepositoryInterface.
     */
    public function getProductIdsInCategory(CategoryInterface $category, QueryBuilder $categoryQb = null);

    /**
     * Count products linked to a node.
     * You can define if you just want to get the property of the actual node
     * or with its children with the direct parameter
     * The third parameter allow to include the actual node or not
     *
     * @param CategoryInterface $category   the requested category node
     * @param QueryBuilder      $categoryQb category query buider
     *
     * @return int
     *
     * @deprecated Will be removed in 1.5. Please use ItemCategoryRepositoryInterface.
     */
    public function getProductsCountInCategory(CategoryInterface $category, QueryBuilder $categoryQb = null);

    /**
     * Apply a filter by product ids
     *
     * @param mixed $qb         query builder to update
     * @param array $productIds product ids
     * @param bool  $include    true for in, false for not in
     */
    public function applyFilterByIds($qb, array $productIds, $include);

    /**
     * Apply a filter by unclassified (not placed in any categories)
     *
     * @param mixed $qb query builder to update
     *
     * @deprecated Will be removed in 1.5. Please use CategoryFilterableRepositoryInterface.
     */
    public function applyFilterByUnclassified($qb);

    /**
     * Apply a filter by category ids
     *
     * @param mixed $qb          query builder to update
     * @param array $categoryIds category ids
     * @param bool  $include     if yes, get product in those categories, if false
     *                           products NOT in those categories
     *
     * @deprecated Will be removed in 1.5. Please use CategoryFilterableRepositoryInterface.
     */
    public function applyFilterByCategoryIds($qb, array $categoryIds, $include = true);

    /**
     * Apply filter by category ids or unclassified
     *
     * @param mixed $qb          query builder to update
     * @param array $categoryIds category ids
     *
     * @deprecated Will be removed in 1.5. Please use CategoryFilterableRepositoryInterface.
     */
    public function applyFilterByCategoryIdsOrUnclassified($qb, array $categoryIds);
}
