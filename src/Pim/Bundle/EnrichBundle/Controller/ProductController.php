<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
class ProductController extends AbstractListCategoryController
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var EntityWithFamilyValuesFillerInterface */
    protected $valuesFiller;

    /**
     * @param TranslatorInterface                   $translator
     * @param ProductRepositoryInterface            $productRepository
     * @param CategoryRepositoryInterface           $categoryRepository
     * @param SaverInterface                        $productSaver
     * @param ProductBuilderInterface               $productBuilder
     * @param EntityWithFamilyValuesFillerInterface $valuesFiller
     * @param SecurityFacade                        $securityFacade
     * @param string                                $categoryClass
     * @param string                                $acl
     * @param string                                $template
     */
    public function __construct(
        TranslatorInterface $translator,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        SaverInterface $productSaver,
        ProductBuilderInterface $productBuilder,
        EntityWithFamilyValuesFillerInterface $valuesFiller,
        string $categoryClass,
        SecurityFacade $securityFacade,
        string $acl,
        string $template
    ) {
        parent::__construct($categoryRepository, $securityFacade, $categoryClass, $acl, $template);

        $this->productRepository = $productRepository;
        $this->translator = $translator;
        $this->productSaver = $productSaver;
        $this->productBuilder = $productBuilder;
        $this->valuesFiller = $valuesFiller;
        $this->acl = $acl;
    }

    /**
     * Toggle product status (enabled/disabled)
     *
     * @param string $id
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_product_edit_attributes")
     */
    public function toggleStatusAction($id)
    {
        $product = $this->findEntityWithCategoriesOr404($id);

        $toggledStatus = !$product->isEnabled();
        $product->setEnabled($toggledStatus);
        $this->productSaver->save($product);

        $successMessage = $toggledStatus ? 'flash.product.enabled' : 'flash.product.disabled';

        return new JsonResponse(
            ['successful' => true, 'message' => $this->translator->trans($successMessage)]
        );
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
    protected function findEntityWithCategoriesOr404(string $id)
    {
        $product = $this->productRepository->find($id);
        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with with ID "%s" could not be found.', $id)
            );
        }
        // With this version of the form we need to add missing values from family
        $this->valuesFiller->fillMissingValues($product);
        $this->productBuilder->addMissingAssociations($product);

        return $product;
    }
}
