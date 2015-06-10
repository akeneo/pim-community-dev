<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product category controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryRestController
{
    /** @var ProductManager */
    protected $productManager;

    /** @var ProductCategoryManager */
    protected $productCatManager;

    /**
     * @param ProductManager         $productManager
     * @param ProductCategoryManager $productCatManager
     */
    public function __construct(
        ProductManager $productManager,
        ProductCategoryManager $productCatManager
    ) {
        $this->productManager    = $productManager;
        $this->productCatManager = $productCatManager;
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
        // TODO use repo for that
        $trees = $this->productCatManager->getProductCountByTree($product);

        $result = [
            'trees'      => [],
            'categories' => []
        ];
        foreach ($trees as $tree) {
            $result['trees'][] = [
                'id'         => $tree['tree']->getId(),
                'code'       => $tree['tree']->getCode(),
                'label'      => $tree['tree']->getLabel(),
                'associated' => $tree['productCount'] > 0
            ];
        }

        foreach ($product->getCategories() as $category) {
            $result['categories'][] = [
                'id'   => $category->getId(),
                'code' => $category->getCode()
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param string $id the product id
     *
     * @return ProductInterface
     *
     * @throws NotFoundHttpException
     */
    protected function findProductOr404($id)
    {
        // TODO use repo for that
        $product = $this->productManager->find($id);

        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
    }
}
