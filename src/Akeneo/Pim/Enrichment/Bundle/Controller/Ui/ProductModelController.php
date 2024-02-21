<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\Ui;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
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
class ProductModelController extends AbstractListCategoryController
{
    /** @var ProductModelRepositoryInterface */
    protected $productModelRepository;

    /**
     * @param ProductModelRepositoryInterface       $productModelRepository
     * @param CategoryRepositoryInterface           $categoryRepository
     * @param SecurityFacade                        $securityFacade
     * @param string                                $categoryClass
     * @param string                                $acl
     * @param string                                $template
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        CategoryRepositoryInterface $categoryRepository,
        SecurityFacade $securityFacade,
        string $categoryClass,
        string $acl,
        string $template
    ) {
        parent::__construct($categoryRepository, $securityFacade, $categoryClass, $acl, $template);

        $this->productModelRepository = $productModelRepository;
    }

    /**
     * List categories associated with the provided product model and descending from the category
     * defined by the parent parameter.
     *
     * httpparam include_category if true, will include the parentCategory in the response
     */
    public function listCategoriesAction(Request $request, string $id, string $categoryId): Response
    {
        return $this->doListCategoriesAction($request, $id, $categoryId);
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
    protected function findEntityWithCategoriesOr404(string $id)
    {
        $productModel = $this->productModelRepository->find($id);
        if (null === $productModel) {
            throw new NotFoundHttpException(
                sprintf('Product model with ID "%s" could not be found.', $id)
            );
        }

        return $productModel;
    }
}
