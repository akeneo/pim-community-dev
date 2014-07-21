<?php

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager as BaseProductCategoryManager;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;

/**
 * Product category manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductCategoryManager extends BaseProductCategoryManager
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var CategoryAccessRepository */
    protected $accessRepository;

    /**
     * Constructor
     *
     * @param ProductCategoryRepositoryInterface $productRepo     Product repository
     * @param CategoryRepository                 $categoryRepo    Category repository
     * @param SecurityContextInterface           $securityContext Security context
     * @param CategoryAccessRepository           $accessRepo      Category access repository
     */
    public function __construct(
        ProductCategoryRepositoryInterface $productRepo,
        CategoryRepository $categoryRepo,
        SecurityContextInterface $securityContext,
        CategoryAccessRepository $accessRepo
    ) {
        parent::__construct($productRepo, $categoryRepo);

        $this->securityContext = $securityContext;
        $this->accessRepository = $accessRepo;
    }

    /**
     * {@inheritdoc}
     * @see getProductCountByTree same logic but here we apply permisions and count only visible category (full path)
     */
    public function getProductCountByGrantedTree(ProductInterface $product)
    {
        $count     = $this->getProductCountWithFullGrantedPath($product);
        $trees     = $this->categoryRepository->getChildren(null, true, 'created', 'DESC');
        $treeCount = [];
        foreach ($trees as $tree) {
            if ($this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $tree)) {
                $treeCount[] = [
                    'tree' => $tree,
                    'productCount' => isset($count[$tree->getId()]) ? $count[$tree->getId()] : 0
                ];
            }
        }

        return $treeCount;
    }

    /**
     * {@inheritdoc}
     * @see getProductsCountInCategory same logic with applying permissions
     */
    public function getProductsCountInGrantedCategory(
        CategoryInterface $category,
        $inChildren = false,
        $inProvided = true
    ) {
        if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
            return 0;
        }

        $grantedQb = null;
        if ($inChildren) {
            $categoryQb = $this->categoryRepository->getAllChildrenQueryBuilder($category, $inProvided);
            $grantedQb = $this->getAllGrantedChildrenQueryBuilder($categoryQb);
        }

        return $this->productRepository->getProductsCountInCategory($category, $grantedQb);
    }

    /**
     * {@inheritdoc}
     * @see getProductIdsInCategory same logic with applying permissions
     */
    public function getProductIdsInGrantedCategory(CategoryInterface $category, $inChildren = false)
    {
        if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
            return [];
        }

        $grantedQb = null;
        if ($inChildren) {
            $categoryQb = $this->categoryRepository->getAllChildrenQueryBuilder($category, true);
            $grantedQb = $this->getAllGrantedChildrenQueryBuilder($categoryQb);
        }

        return $this->productRepository->getProductIdsInCategory($category, $grantedQb);
    }

    /**
     * @param mixed $queryBuilder
     */
    public function addFilterByAll($queryBuilder)
    {
        $user = $this->securityContext->getUser();
        $grantedCategories = $this->accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS);
        $this->productRepository->addFilterByAll($queryBuilder, $grantedCategories);
    }

    /**
     * Count only product with a full accessible path
     *
     * @param ProductInterface $product
     *
     * @return array with format [treeId => productCount]
     */
    protected function getProductCountWithFullGrantedPath(ProductInterface $product)
    {
        $categories = $product->getCategories();
        $treesCount = [];
        foreach ($categories as $category) {
            $path = $this->categoryRepository->getPath($category);
            $fullPathGranted = true;
            foreach ($path as $pathItem) {
                if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $pathItem)) {
                    $fullPathGranted = false;
                    break;
                }
            }
            if ($fullPathGranted) {
                $treeId = $category->getRoot();
                if (!isset($treesCount[$treeId])) {
                    $treesCount[$treeId] = 0;
                }
                $treesCount[$treeId]++;
            }
        }

        return $treesCount;
    }

    /**
     * Build a new query builder based on children QB to let only granted children
     *
     * @param QueryBuilder $childrenQb
     *
     * @return QueryBuilder
     */
    protected function getAllGrantedChildrenQueryBuilder(QueryBuilder $childrenQb)
    {
        $categories = $childrenQb->getQuery()->execute();
        foreach ($categories as $index => $category) {
            if (!$this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
                unset($categories[$index]);
            }
        }

        $rootAlias  = current($childrenQb->getRootAliases());
        $grantedQb = $this->categoryRepository->createQueryBuilder($rootAlias);
        $grantedQb->select($rootAlias.'.id');
        $grantedQb->where($grantedQb->expr()->in($rootAlias.'.id', ':categories'));
        $grantedQb->setParameter('categories', $categories);

        return $grantedQb;
    }
}
