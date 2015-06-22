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

    public function __construct(
        CategoryRepositoryInterface $productCategoryRepo,
        ItemCategoryRepositoryInterface $itemProductCatRepo,
        CategoryRepositoryInterface $assetCategoryRepo,
        ItemCategoryRepositoryInterface $itemAssetCatRepo,
        $productsLimitForRemoval = null
    ) {
        parent::__construct($productCategoryRepo, $itemProductCatRepo, $assetCategoryRepo);

        $this->assetCategoryRepo = $assetCategoryRepo;
        $this->itemAssetCatRepo = $itemAssetCatRepo;
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
        if ('asset' === $relatedEntity) {
            return $this->assetCategoryRepo;
        }

        return parent::getRelatedEntityRepo($relatedEntity);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRelatedCategoryEntityRepo($relatedEntity)
    {
        if ('asset' === $relatedEntity) {
            return $this->itemAssetCatRepo;
        }

        return parent::getRelatedCategoryEntityRepo($relatedEntity);
    }
}
