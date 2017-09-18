<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var string */
    protected $categoryClass;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var EntityWithFamilyValuesFillerInterface */
    protected $valuesFiller;

    /**
     * @param TranslatorInterface                   $translator
     * @param ProductRepositoryInterface            $productRepository
     * @param CategoryRepositoryInterface           $categoryRepository
     * @param SaverInterface                        $productSaver
     * @param ProductBuilderInterface               $productBuilder
     * @param EntityWithFamilyValuesFillerInterface $valuesFiller
     * @param string                                $categoryClass
     */
    public function __construct(
        TranslatorInterface $translator,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        SaverInterface $productSaver,
        ProductBuilderInterface $productBuilder,
        EntityWithFamilyValuesFillerInterface $valuesFiller,
        $categoryClass
    ) {
        $this->translator         = $translator;
        $this->productRepository  = $productRepository;
        $this->productSaver       = $productSaver;
        $this->productBuilder     = $productBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->valuesFiller       = $valuesFiller;
        $this->categoryClass      = $categoryClass;
    }

    /**
     * Toggle product status (enabled/disabled)
     *
     * @param int $id
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_product_edit_attributes")
     */
    public function toggleStatusAction($id)
    {
        $product = $this->findProductOr404($id);

        $toggledStatus = !$product->isEnabled();
        $product->setEnabled($toggledStatus);
        $this->productSaver->save($product);

        $successMessage = $toggledStatus ? 'flash.product.enabled' : 'flash.product.disabled';

        return new JsonResponse(
            ['successful' => true, 'message' => $this->translator->trans($successMessage)]
        );
    }

    /**
     * List categories associated with the provided product and descending from the category
     * defined by the parent parameter.
     *
     * @param Request    $request    The request object
     * @param int|string $id         Product id
     * @param int        $categoryId The parent category id
     *
     * httpparam include_category if true, will include the parentCategory in the response
     *
     * @Template
     * @AclAncestor("pim_enrich_product_categories_view")
     *
     * @return array
     */
    public function listCategoriesAction(Request $request, $id, $categoryId)
    {
        $product = $this->findProductOr404($id);
        $parent = $this->categoryRepository->find($categoryId);

        if (null === $parent) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $this->categoryClass));
        }

        $categories = null;
        $selectedCategoryIds = $request->get('selected', null);
        if (null !== $selectedCategoryIds) {
            $categories = $this->categoryRepository->getCategoriesByIds($selectedCategoryIds);
        } elseif (null !== $product) {
            $categories = $product->getCategories();
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
    protected function getFilledTree(CategoryInterface $parent, Collection $categories)
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
        // With this version of the form we need to add missing values from family
        $this->valuesFiller->fillMissingValues($product);
        $this->productBuilder->addMissingAssociations($product);

        return $product;
    }
}
