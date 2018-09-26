<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product model category controller
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCategoryController
{
    /** @var ProductModelRepositoryInterface */
    protected $productModelRepository;

    /** @var ItemCategoryRepositoryInterface */
    protected $productModelCategoryRepository;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param ItemCategoryRepositoryInterface $productModelCategoryRepository
     * @param ObjectFilterInterface           $objectFilter
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        ItemCategoryRepositoryInterface $productModelCategoryRepository,
        ObjectFilterInterface $objectFilter
    ) {
        $this->productModelRepository         = $productModelRepository;
        $this->productModelCategoryRepository = $productModelCategoryRepository;
        $this->objectFilter                   = $objectFilter;
    }

    /**
     * List categories and trees for a product model
     *
     * @param string $id
     *
     * @AclAncestor("pim_enrich_product_model_categories_view")
     *
     * @return JsonResponse
     */
    public function listAction($id): JsonResponse
    {
        $productModel = $this->findProductModelOr404($id);
        $trees = $this->productModelCategoryRepository->getItemCountByTree($productModel);

        $result['trees'] = $this->buildTrees($trees);
        $result['categories'] = $this->buildCategories($productModel);

        return new JsonResponse($result);
    }

    /**
     * Find a product model by its id or return a 404 response
     *
     * @param string $id the product id
     *
     * @throws NotFoundHttpException
     *
     * @return ProductModelInterface
     */
    protected function findProductModelOr404(string $id): ProductModelInterface
    {
        $productModel = $this->productModelRepository->find($id);

        if (null === $productModel) {
            throw new NotFoundHttpException(
                sprintf('Product model with ID "%s" could not be found.', $id)
            );
        }

        return $productModel;
    }

    /**
     * @param array $trees
     *
     * @return array
     */
    protected function buildTrees(array $trees): array
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
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    protected function buildCategories(ProductModelInterface $productModel): array
    {
        $result = [];

        foreach ($productModel->getCategories() as $category) {
            $result[] = [
                'id'     => $category->getId(),
                'code'   => $category->getCode(),
                'rootId' => $category->getRoot(),
            ];
        }

        return $result;
    }
}
