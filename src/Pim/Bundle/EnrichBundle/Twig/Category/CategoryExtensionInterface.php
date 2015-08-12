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
interface CategoryExtensionInterface
{
    /**
     * Get category repository
     *
     * @return CategoryRepositoryInterface
     */
    public function getCategoryRepo();

    /**
     * Get item category repository
     *
     * @return ItemCategoryRepositoryInterface
     */
    public function getItemCategoryRepo();

    /**
     * @return int
     */
    public function getProductsLimitForRemoval();
}
