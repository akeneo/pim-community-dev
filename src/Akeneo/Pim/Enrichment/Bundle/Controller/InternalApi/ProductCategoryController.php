<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product category controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryController
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var ProductCategoryRepositoryInterface */
    protected $productCategoryRepository;

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
        $this->productRepository         = $productRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->objectFilter              = $objectFilter;
    }

    /**
     * List categories and trees for a product
     *
     * @param string $id
     *
     * @AclAncestor("pim_enrich_product_categories_view")
     *
     * @return JsonResponse
     */
    public function listAction($id)
    {
        $product = $this->findProductOr404($id);
        $trees = $this->productCategoryRepository->getItemCountByTree($product);

        $result['trees'] = $this->buildTrees($trees);
        $result['categories'] = $this->buildCategories($product);

        return new JsonResponse($result);
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param string $id the product id
     *
     * @throws NotFoundHttpException
     *
     * @return ProductInterface
     */
    protected function findProductOr404($id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
    }

    /**
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

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function buildCategories(ProductInterface $product)
    {
        $result = [];

        foreach ($product->getCategories() as $category) {
            $result[] = [
                'id'     => $category->getId(),
                'code'   => $category->getCode(),
                'rootId' => $category->getRoot(),
            ];
        }

        return $result;
    }
}
