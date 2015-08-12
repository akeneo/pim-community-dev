<?php

namespace Pim\Bundle\EnrichBundle\Twig\Category;

use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Component\Classification\Repository\ItemCategoryRepositoryInterface;

/**
 * Category extension to render category from twig templates
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryExtension implements CategoryExtensionInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepo;

    /** @var ItemCategoryRepositoryInterface */
    protected $itemCategoryRepo;

    /** @var int */
    protected $productsLimitForRemoval;

    /**
     * @param CategoryRepositoryInterface     $categoryRepo
     * @param ItemCategoryRepositoryInterface $itemCategoryRepo
     * @param int                             $productsLimitForRemoval
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepo,
        ItemCategoryRepositoryInterface $itemCategoryRepo,
        $productsLimitForRemoval = null
    ) {
        $this->categoryRepo            = $categoryRepo;
        $this->itemCategoryRepo        = $itemCategoryRepo;
        $this->productsLimitForRemoval = $productsLimitForRemoval;
    }

    /**
     * (@inheritdoc}
     */
    public function getCategoryRepo()
    {
        return $this->categoryRepo;
    }

    /**
     * (@inheritdoc}
     */
    public function getItemCategoryRepo()
    {
        return $this->itemCategoryRepo;
    }

    /**
     * (@inheritdoc}
     */
    public function getProductsLimitForRemoval()
    {
        return $this->productsLimitForRemoval;
    }
}
