<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\Manager\SequentialEditManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
    const BACK_TO_GRID = 'BackGrid';

    const CREATE = 'Create';

    const SAVE_AND_NEXT = 'SaveAndNext';

    const SAVE_AND_FINISH = 'SaveAndFinish';

    /** @var RouterInterface */
    protected $router;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var UserContext */
    protected $userContext;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var SequentialEditManager */
    protected $seqEditManager;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var string */
    protected $categoryClass;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param RouterInterface             $router
     * @param TokenStorageInterface       $tokenStorage
     * @param FormFactoryInterface        $formFactory
     * @param TranslatorInterface         $translator
     * @param ProductRepositoryInterface  $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UserContext                 $userContext
     * @param SecurityFacade              $securityFacade
     * @param SaverInterface              $productSaver
     * @param SequentialEditManager       $seqEditManager
     * @param ProductBuilderInterface     $productBuilder
     * @param string                      $categoryClass
     */
    public function __construct(
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        UserContext $userContext,
        SecurityFacade $securityFacade,
        SaverInterface $productSaver,
        SequentialEditManager $seqEditManager,
        ProductBuilderInterface $productBuilder,
        $categoryClass
    ) {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->productRepository = $productRepository;
        $this->userContext = $userContext;
        $this->securityFacade = $securityFacade;
        $this->productSaver = $productSaver;
        $this->seqEditManager = $seqEditManager;
        $this->productBuilder = $productBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->categoryClass = $categoryClass;
    }

    /**
     * List products
     *
     * @AclAncestor("pim_enrich_product_index")
     * @Template
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $this->seqEditManager->removeByUser($this->tokenStorage->getToken()->getUser());

        return [
            'locales'    => $this->getUserLocales(),
            'dataLocale' => $this->getDataLocale(),
        ];
    }

    /**
     * Edit product
     *
     * @Template
     * @AclAncestor("pim_enrich_product_index")
     *
     * @return array
     */
    public function editAction()
    {
        return [];
    }

    /**
     * Toggle product status (enabled/disabled)
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_product_edit_attributes")
     */
    public function toggleStatusAction(Request $request, $id)
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
     * {@inheritdoc}
     */
    protected function redirectToRoute($route, $parameters = [])
    {
        if (!isset($parameters['dataLocale'])) {
            $parameters['dataLocale'] = $this->userContext->getCurrentLocaleCode();
        }

        return new JsonResponse(['route' => $route, 'params' => $parameters]);
    }

    /**
     * @return LocaleInterface[]
     */
    protected function getUserLocales()
    {
        return $this->userContext->getUserLocales();
    }

    /**
     * Get data locale code
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getDataLocaleCode()
    {
        return $this->userContext->getCurrentLocaleCode();
    }

    /**
     * Get data locale object
     *
     * @throws \Exception
     *
     * @return LocaleInterface
     */
    protected function getDataLocale()
    {
        return $this->userContext->getCurrentLocale();
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
        $product = $this->productRepository->findOneByWithValues($id);
        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }
        // With this version of the form we need to add missing values from family
        $this->productBuilder->addMissingProductValues($product);
        $this->productBuilder->addMissingAssociations($product);

        return $product;
    }

    /**
     * Returns the options for the create form
     *
     * @return array
     */
    protected function getCreateFormOptions()
    {
        return [];
    }
}
