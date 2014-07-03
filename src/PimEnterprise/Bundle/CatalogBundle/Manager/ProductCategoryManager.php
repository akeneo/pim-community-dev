<?php

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager as BaseProductCategoryManager;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

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
            if (false === $this->securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $tree['tree'])) {
                unset($trees[$key]);
            }
        }

        return $trees;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true)
    {
        if (false === $this->securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $category)) {
            return 0;
        }
        // TODO : deal with children

        return parent::getProductsCountInCategory($category, $inChildren, $inProvided);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsInCategory(CategoryInterface $category, $inChildren = false)
    {
        if (false === $this->securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $category)) {
            return [];
        }
        // TODO : deal with children

        return parent::getProductIdsInCategory($category, $inChildren);
    }
}
