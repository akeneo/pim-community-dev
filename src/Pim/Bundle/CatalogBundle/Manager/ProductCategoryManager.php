<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product category manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryManager
{
    /**
     * @var ProductCategoryRepositoryInterface $productRepository
     */
    protected $productRepository;

    /**
     * @var CategoryRepository $categoryRepository
     */
    protected $categoryRepository;

    /**
     * Constructor
     *
     * @param ProductCategoryRepositoryInterface $productRepo  Product repository
     * @param CategoryRepository                 $categoryRepo Category repository
     */
    public function __construct(ProductCategoryRepositoryInterface $productRepo, CategoryRepository $categoryRepo)
    {
        $this->productRepository = $productRepo;
        $this->categoryRepository = $categoryRepo;
    }

    /**
     * @return ProductCategoryRepositoryInterface
     */
    public function getProductCategoryRepository()
    {
        return $this->productRepository;
    }

    /**
     * @return CategoryInterface
     */
    public function getCategoryRepository()
    {
        return $this->categoryRepository;
    }

    /**
     * Count products linked to a node.
     * You can define if you just want to get the property of the actual node
     * or with its children with the direct parameter
     * The third parameter allow to include the actual node or not
     *
     * @param CategoryInterface $category   the requested category node
     * @param boolean           $inChildren true to include children in count
     * @param boolean           $inProvided true to include the provided none to count product
     *
     * @return integer
     */
    public function getProductsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true)
    {
        $categoryQb = null;
        if ($inChildren) {
            $categoryQb = $this->categoryRepository->getAllChildrenQueryBuilder($category, $inProvided);
        }

        return $this->productRepository->getProductsCountInCategory($category, $categoryQb);
    }

    /**
     * Get product ids linked to a category or its children.
     * You can define if you just want to get the property of the actual node or with its children with the direct
     * parameter
     *
     * @param CategoryInterface $category   the requested node
     * @param boolean           $inChildren true to take children not into account
     *
     * @return array
     */
    public function getProductIdsInCategory(CategoryInterface $category, $inChildren = false)
    {
        $categoryQb = null;
        if ($inChildren) {
            $categoryQb = $this->categoryRepository->getAllChildrenQueryBuilder($category, true);
        }

        return $this->productRepository->getProductIdsInCategory($category, $categoryQb);
    }

    /**
     * Return the number of times the product is present in each tree
     *
     * @param ProductInterface $product The product to look for in the trees
     *
     * @return array Each row of the array has the format:'tree'=>treeObject, 'productCount'=>integer
     */
    public function getProductCountByTree(ProductInterface $product)
    {
        return $this->productRepository->getProductCountByTree($product);
    }
}
