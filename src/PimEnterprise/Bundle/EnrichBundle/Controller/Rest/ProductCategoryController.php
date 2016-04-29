<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\EnrichBundle\Controller\Rest\ProductCategoryController as BaseProductCategoryController;
use Pim\Component\Catalog\Repository\ProductCategoryRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * Overridden product category controller
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductCategoryController extends BaseProductCategoryController
{
    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /**
     * @param ProductRepositoryInterface         $productRepository
     * @param ProductCategoryRepositoryInterface $productCategoryRepository
     * @param ObjectFilterInterface              $objectFilter
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductCategoryRepositoryInterface $productCategoryRepository,
        ObjectFilterInterface $objectFilter
    ) {
        parent::__construct($productRepository, $productCategoryRepository);

        $this->objectFilter = $objectFilter;
    }

    /**
     * Overridden trees to return only granted categories 
     *
     * @param array $trees
     *
     * @return array
     */
    protected function buildTrees(array $trees)
    {
        $result = [];

        foreach ($trees as $tree) {
            $category = $tree['tree'];

            if (!$this->objectFilter->filterObject($category, 'pim.internal_api.product_category.view')) {
                $result[] = [
                    'id'         => $category->getId(),
                    'code'       => $category->getCode(),
                    'label'      => $category->getLabel(),
                    'associated' => $tree['itemCount'] > 0
                ];
            }
        }

        return $result;
    }
}
