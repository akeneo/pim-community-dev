<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product Model Controller
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelController
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var string */
    private $categoryClass;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var EntityWithFamilyValuesFillerInterface */
    private $valuesFiller;

    /**
     * @param ProductModelRepositoryInterface       $productRepository
     * @param CategoryRepositoryInterface           $categoryRepository
     * @param ProductBuilderInterface               $productBuilder
     * @param EntityWithFamilyValuesFillerInterface $valuesFiller
     * @param string                                $categoryClass
     */
    public function __construct(
        ProductModelRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        ProductBuilderInterface $productBuilder,
        EntityWithFamilyValuesFillerInterface $valuesFiller,
        $categoryClass
    ) {
        $this->productModelRepository  = $productRepository;
        $this->productBuilder     = $productBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->valuesFiller       = $valuesFiller;
        $this->categoryClass      = $categoryClass;
    }

    /**
     * List categories associated with the provided product model and descending from the category
     * defined by the parent parameter.
     *
     * @param Request    $request    The request object
     * @param int|string $id         Product model id
     * @param int        $categoryId The parent category id
     *
     * httpparam include_category if true, will include the parentCategory in the response
     *
     * @Template
     * @AclAncestor("pim_enrich_product_model_categories_view")
     *
     * @return array
     */
    public function listCategoriesAction(Request $request, $id, $categoryId)
    {
        $productModel = $this->findProductModelOr404($id);
        $parent = $this->categoryRepository->find($categoryId);

        if (null === $parent) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $this->categoryClass));
        }

        $categories = null;
        $selectedCategoryIds = $request->get('selected', null);
        if (null !== $selectedCategoryIds) {
            $categories = $this->categoryRepository->getCategoriesByIds($selectedCategoryIds);
        } elseif (null !== $productModel) {
            $categories = $productModel->getCategories();
        }

        $trees = $this->getFilledTree($parent, $categories);

        return ['trees' => $trees, 'categories' => $categories];
    }

    /**
     * Fetch the filled tree
     *
     * @param CategoryInterface $parent
     * @param Collection        $categories
     *
     * @return CategoryInterface[]
     */
    private function getFilledTree(CategoryInterface $parent, Collection $categories)
    {
        return $this->categoryRepository->getFilledTree($parent, $categories);
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param int $id the product id
     *
     * @throws NotFoundHttpException
     *
     * @return ProductModelInterface
     */
    private function findProductModelOr404($id)
    {
        $productModel = $this->productModelRepository->find($id);
        if (null === $productModel) {
            throw new NotFoundHttpException(
                sprintf('Product model with id %s could not be found.', (string) $id)
            );
        }
        // With this version of the form we need to add missing values from family
        $this->valuesFiller->fillMissingValues($productModel);

        return $productModel;
    }
}
