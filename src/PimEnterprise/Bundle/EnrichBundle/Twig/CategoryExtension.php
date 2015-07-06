<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Twig;

use Pim\Bundle\EnrichBundle\Twig\CategoryExtension as BaseCategoryExtension;
use Pim\Component\Classification\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Component\Classification\Repository\ItemCategoryRepositoryInterface;

/**
 * Overriden Twig extension to allow to count products or published products
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class CategoryExtension extends BaseCategoryExtension
{
    /** @var CategoryRepositoryInterface */
    protected $assetCategoryRepo;

    /** @var ItemCategoryRepositoryInterface */
    protected $itemAssetCatRepo;

    /** @var ItemCategoryRepositoryInterface */
    protected $itemPublishedCatRepo;

    /**
     * @param CategoryRepositoryInterface     $productCategoryRepo
     * @param ItemCategoryRepositoryInterface $itemProductCatRepo
     * @param CategoryRepositoryInterface     $assetCategoryRepo
     * @param ItemCategoryRepositoryInterface $itemAssetCatRepo
     * @param ItemCategoryRepositoryInterface $itemPublishedCatRepo
     * @param int|null                        $productsLimitForRemoval
     */
    public function __construct(
        CategoryRepositoryInterface $productCategoryRepo,
        ItemCategoryRepositoryInterface $itemProductCatRepo,
        CategoryRepositoryInterface $assetCategoryRepo,
        ItemCategoryRepositoryInterface $itemAssetCatRepo,
        ItemCategoryRepositoryInterface $itemPublishedCatRepo,
        $productsLimitForRemoval = null
    ) {
        parent::__construct($productCategoryRepo, $itemProductCatRepo, $assetCategoryRepo);

        $this->assetCategoryRepo    = $assetCategoryRepo;
        $this->itemAssetCatRepo     = $itemAssetCatRepo;
        $this->itemPublishedCatRepo = $itemPublishedCatRepo;
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Make the permissions work again with PIM-4292
     */
    protected function countItems(CategoryInterface $category, $includeSub, $relatedEntity)
    {
        return parent::countItems($category, $includeSub, $relatedEntity);
//        if ($relatedEntity === 'published_product') {
//            return $this->publishedManager->getProductsCountInGrantedCategory($category, $includeSub);
//        } else {
//            return $this->manager->getProductsCountInGrantedCategory($category, $includeSub);
//        }

//        if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
//            return 0;
//        }
//
//        $grantedQb = null;
//        if ($inChildren) {
//            $categoryQb = $this->categoryRepository->getAllChildrenQueryBuilder($category, $inProvided);
//            $grantedQb = $this->getAllGrantedChildrenQueryBuilder($categoryQb);
//        }
//
//        return $this->productRepository->getProductsCountInCategory($category, $grantedQb);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRelatedEntityRepo($relatedEntity)
    {
        switch ($relatedEntity) {
            case 'asset':
                return $this->assetCategoryRepo;
            case 'published_product':
                return $this->productCategoryRepo;
        }

        return parent::getRelatedEntityRepo($relatedEntity);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRelatedCategoryEntityRepo($relatedEntity)
    {
        switch ($relatedEntity) {
            case 'asset':
                return $this->itemAssetCatRepo;
            case 'published_product':
                return $this->itemPublishedCatRepo;
        }

        return parent::getRelatedCategoryEntityRepo($relatedEntity);
    }
}
