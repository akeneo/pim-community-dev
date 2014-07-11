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

/**
 * Product category manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductCategoryManager extends BaseProductCategoryManager
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param ProductCategoryRepositoryInterface $productRepo     Product repository
     * @param CategoryRepository                 $categoryRepo    Category repository
     * @param SecurityContextInterface           $securityContext Security context
     */
    public function __construct(
        ProductCategoryRepositoryInterface $productRepo,
        CategoryRepository $categoryRepo,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct($productRepo, $categoryRepo);

        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByTree(ProductInterface $product)
    {
        $trees =  $this->productRepository->getProductCountByTree($product);

        foreach ($trees as $key => $tree) {
            if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $tree['tree'])) {
                unset($trees[$key]);
            }
        }

        return $trees;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInGrantedCategory(CategoryInterface $category, $inChildren = false, $inProvided = true)
    {
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
        $rootEntity = current($childrenQb->getRootEntities());
        $grantedQb = $this->categoryRepository->createQueryBuilder($rootAlias);
        $grantedQb->select($rootAlias.'.id');
        $grantedQb->where($grantedQb->expr()->in($rootAlias.'.id', ':categories'));
        $grantedQb->setParameter('categories', $categories);

        return $grantedQb;
    }
}
