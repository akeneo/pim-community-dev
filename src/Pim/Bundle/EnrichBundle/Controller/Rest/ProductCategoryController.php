<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
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

    /**
     * @param ProductRepositoryInterface         $productRepository
     * @param ProductCategoryRepositoryInterface $productCategoryRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductCategoryRepositoryInterface $productCategoryRepository
    ) {
        $this->productRepository         = $productRepository;
        $this->productCategoryRepository = $productCategoryRepository;
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
        $trees   = $this->productCategoryRepository->getProductCountByTree($product);

        $result = [
            'trees'      => [],
            'categories' => []
        ];
        foreach ($trees as $tree) {
            $result['trees'][] = [
                'id'         => $tree['tree']->getId(),
                'code'       => $tree['tree']->getCode(),
                'label'      => $tree['tree']->getLabel(),
                'associated' => $tree['itemCount'] > 0
            ];
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
        $product = $this->productRepository->findOneByWithValues($id);

        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
    }
}
