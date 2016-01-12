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

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\EnrichBundle\Controller\Rest\ProductCategoryController as BaseProductCategoryController;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        ObjectFilterInterface $objectFilter = null
    ) {
        parent::__construct($productRepository, $productCategoryRepository);

        $this->objectFilter = $objectFilter;
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_enrich_product_categories_view")
     */
    public function listAction($id)
    {
        $product = $this->findProductOr404($id);
        $trees   = $this->productCategoryRepository->getProductCountByTree($product);

        $result = [
            'trees'      => [],
            'categories' => []
        ];
        foreach ($trees as $tree) {
            $category = $tree['tree'];

            if (null === $this->objectFilter || (null !== $this->objectFilter &&
                !$this->objectFilter->filterObject($category, 'pim.internal_api.product_category.view'))) {

                $result['trees'][] = [
                    'id'         => $category->getId(),
                    'code'       => $category->getCode(),
                    'label'      => $category->getLabel(),
                    'associated' => $tree['itemCount'] > 0
                ];
            }
        }

        foreach ($product->getCategories() as $category) {
            $result['categories'][] = [
                'id'     => $category->getId(),
                'code'   => $category->getCode(),
                'rootId' => $category->getRoot(),
            ];
        }

        return new JsonResponse($result);
    }
}
