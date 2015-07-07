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

use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\EnrichBundle\Twig\CategoryExtension as BaseCategoryExtension;

/**
 * Overriden Twig extension to allow to count products or published products
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class CategoryExtension extends BaseCategoryExtension
{
    /**
     * @var ProductCategoryManager
     */
    protected $publishedManager;

    /**
     * Constructor
     *
     * @param ProductCategoryManager $manager
     * @param ProductCategoryManager $publishedManager
     * @param int|null               $productsLimitForRemoval
     */
    public function __construct(
        ProductCategoryManager $manager,
        ProductCategoryManager $publishedManager,
        $productsLimitForRemoval = null
    ) {
        parent::__construct($manager, $productsLimitForRemoval);
        $this->publishedManager        = $publishedManager;
        $this->productsLimitForRemoval = $productsLimitForRemoval;
    }

    /**
     * {@inheritdoc}
     */
    protected function countProducts(CategoryInterface $category, $includeSub, $relatedEntity)
    {
        if ($relatedEntity === 'published_product') {
            return $this->publishedManager->getProductsCountInGrantedCategory($category, $includeSub);
        } else {
            return $this->manager->getProductsCountInGrantedCategory($category, $includeSub);
        }
    }
}
