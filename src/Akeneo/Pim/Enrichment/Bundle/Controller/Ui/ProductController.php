<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\Ui;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController extends AbstractListCategoryController
{
    protected TranslatorInterface $translator;
    protected ProductRepositoryInterface $productRepository;
    protected SaverInterface $productSaver;

    public function __construct(
        TranslatorInterface $translator,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        SaverInterface $productSaver,
        string $categoryClass,
        SecurityFacade $securityFacade,
        string $acl,
        string $template
    ) {
        parent::__construct($categoryRepository, $securityFacade, $categoryClass, $acl, $template);

        $this->productRepository = $productRepository;
        $this->translator = $translator;
        $this->productSaver = $productSaver;
        $this->acl = $acl;
    }

    /**
     * List categories associated with the provided product and descending from the category
     * defined by the parent parameter.
     *
     * httpparam include_category if true, will include the parentCategory in the response
     */
    public function listCategoriesAction(Request $request, string $uuid, string $categoryId): Response
    {
        return $this->doListCategoriesAction($request, $uuid, $categoryId);
    }

    /**
     * Toggle product status (enabled/disabled)
     *
     * @AclAncestor("pim_enrich_product_edit_attributes")
     */
    public function toggleStatusAction(string $uuid): JsonResponse
    {
        $product = $this->findEntityWithCategoriesOr404($uuid);

        $toggledStatus = !$product->isEnabled();
        $product->setEnabled($toggledStatus);
        $this->productSaver->save($product);

        $successMessage = $toggledStatus ? 'flash.product.enabled' : 'flash.product.disabled';

        return new JsonResponse(
            ['successful' => true, 'message' => $this->translator->trans($successMessage)]
        );
    }

    /**
     * Find a product by its uuid or return a 404 response
     *
     * @throws NotFoundHttpException
     */
    protected function findEntityWithCategoriesOr404(string $uuid): ProductInterface
    {
        $product = $this->productRepository->find($uuid);
        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with uuid "%s" could not be found.', $uuid)
            );
        }

        return $product;
    }
}
