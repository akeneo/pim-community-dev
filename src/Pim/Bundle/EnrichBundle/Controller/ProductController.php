<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\Classification\Factory\CategoryFactory;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\MediaManagementException;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use Pim\Bundle\EnrichBundle\Manager\SequentialEditManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController extends AbstractDoctrineController
{
    /** @var ProductManager */
    protected $productManager;

    /** @var CategoryManager */
    protected $categoryManager;

    /** @var ProductCategoryManager */
    protected $productCatManager;

    /** @var UserContext */
    protected $userContext;

    /** @var VersionManager */
    protected $versionManager;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var SequentialEditManager */
    protected $seqEditManager;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var CategoryFactory */
    protected $categoryFactory;

    /**
     * Constant used to redirect to the datagrid when save edit form
     *
     * @staticvar string
     */
    const BACK_TO_GRID = 'BackGrid';

    /**
     * Constant used to redirect to create popin when save edit form
     *
     * @staticvar string
     */
    const CREATE = 'Create';

    /**
     * Constant used to redirect to next product in a sequential edition
     *
     * @staticvar string
     */
    const SAVE_AND_NEXT = 'SaveAndNext';

    /**
     * Constant used to redirect to the grid once all products are edited in a sequential edition
     *
     * @staticvar string
     */
    const SAVE_AND_FINISH = 'SaveAndFinish';

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param TokenStorageInterface    $tokenStorage
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param ProductManager           $productManager
     * @param CategoryManager          $categoryManager
     * @param UserContext              $userContext
     * @param VersionManager           $versionManager
     * @param SecurityFacade           $securityFacade
     * @param ProductCategoryManager   $prodCatManager
     * @param SaverInterface           $productSaver
     * @param SequentialEditManager    $seqEditManager
     * @param ProductBuilderInterface  $productBuilder
     * @param CategoryFactory          $categoryFactory
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        ProductManager $productManager,
        CategoryManager $categoryManager,
        UserContext $userContext,
        VersionManager $versionManager,
        SecurityFacade $securityFacade,
        ProductCategoryManager $prodCatManager,
        SaverInterface $productSaver,
        SequentialEditManager $seqEditManager,
        ProductBuilderInterface $productBuilder,
        CategoryFactory $categoryFactory
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $tokenStorage,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->productManager    = $productManager;
        $this->categoryManager   = $categoryManager;
        $this->userContext       = $userContext;
        $this->versionManager    = $versionManager;
        $this->securityFacade    = $securityFacade;
        $this->productCatManager = $prodCatManager;
        $this->productSaver      = $productSaver;
        $this->seqEditManager    = $seqEditManager;
        $this->productBuilder    = $productBuilder;
        $this->categoryFactory   = $categoryFactory;
    }

    /**
     * List products
     *
     * @param Request $request the request
     *
     * @AclAncestor("pim_enrich_product_index")
     * @Template
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $this->seqEditManager->removeByUser($this->getUser());

        return [
            'locales'    => $this->getUserLocales(),
            'dataLocale' => $this->getDataLocale(),
        ];
    }

    /**
     * Create product
     *
     * @param Request $request
     * @param string  $dataLocale
     *
     * @Template
     * @AclAncestor("pim_enrich_product_create")
     *
     * @return array
     */
    public function createAction(Request $request, $dataLocale)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_product_index');
        }

        $product = $this->productBuilder->createProduct();
        $form    = $this->createForm('pim_product_create', $product, $this->getCreateFormOptions($product));
        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->productSaver->save($product);
                $this->addFlash('success', 'flash.product.created');

                if ($dataLocale === null) {
                    $dataLocale = $this->getDataLocaleCode();
                }

                $url = $this->generateUrl(
                    'pim_enrich_product_edit',
                    ['id' => $product->getId(), 'dataLocale' => $dataLocale]
                );
                $response = ['status' => 1, 'url' => $url];

                return new Response(json_encode($response));
            }
        }

        return [
            'form'       => $form->createView(),
            'dataLocale' => $this->getDataLocaleCode()
        ];
    }

    /**
     * Edit product
     *
     * @param Request $request
     * @param int     $id
     *
     * @Template
     * @AclAncestor("pim_enrich_product_index")
     *
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        return [];
    }

    /**
     * Toggle product status (enabled/disabled)
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response|RedirectResponse
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

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                ['successful' => true, 'message' => $this->translator->trans($successMessage)]
            );
        } else {
            return $this->redirectToRoute('pim_enrich_product_index');
        }
    }

    /**
     * Switch case to redirect after saving a product from the edit form
     *
     * @param array $params
     *
     * @return Response
     */
    protected function redirectAfterEdit($params)
    {
        switch ($this->getRequest()->get('action')) {
            case self::CREATE:
                $route                  = 'pim_enrich_product_edit';
                $params['create_popin'] = true;
                break;
        }

        return $this->redirectToRoute($route, $params);
    }

    /**
     * History of a product
     *
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("pim_enrich_product_history")
     *
     * @return Response
     */
    public function historyAction(Request $request, $id)
    {
        return $this->render(
            'PimEnrichBundle:Product:_history.html.twig',
            [
                'product' => $this->findProductOr404($id),
            ]
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
        $parent = $this->findOr404($this->categoryFactory->getCategoryClass(), $categoryId);
        $categories = null;

        $includeParent = $request->get('include_parent', false);
        $includeParent = ($includeParent === 'true');

        $selectedCategoryIds = $request->get('selected', null);
        if (null !== $selectedCategoryIds) {
            $categories = $this->categoryManager->getCategoriesByIds($selectedCategoryIds);
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
        return $this->categoryManager->getFilledTree($parent, $categories);
    }

    /**
     * Fetch the product count by tree
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getProductCountByTree(ProductInterface $product)
    {
        return $this->productCatManager->getProductCountByTree($product);
    }

    /**
     * {@inheritdoc}
     */
    protected function redirectToRoute($route, $parameters = [], $status = 302)
    {
        if (!isset($parameters['dataLocale'])) {
            $parameters['dataLocale'] = $this->getDataLocaleCode();
        }

        return parent::redirectToRoute($route, $parameters, $status);
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
     * @return string
     */
    protected function getComparisonLocale()
    {
        $locale = $this->getRequest()->query->get('compareWith');

        if ($this->getDataLocaleCode() !== $locale) {
            return $locale;
        }
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
        $product = $this->productManager->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }
        // With this version of the form we need to add missing values from family
        $this->productBuilder->addMissingProductValues($product);
        $this->productBuilder->addMissingAssociations($product);

        return $product;
    }

    /**
     * Get the AvailbleAttributes form
     *
     * @param array               $attributes          The attributes
     * @param AvailableAttributes $availableAttributes The available attributes container
     *
     * @return Form
     */
    protected function getAvailableAttributesForm(
        array $attributes = [],
        AvailableAttributes $availableAttributes = null
    ) {
        return $this->createForm(
            'pim_available_attributes',
            $availableAttributes ?: new AvailableAttributes(),
            ['excluded_attributes' => $attributes]
        );
    }

    /**
     * Returns the options for the edit form
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getEditFormOptions(ProductInterface $product)
    {
        return [
            'enable_values'    => $this->securityFacade->isGranted('pim_enrich_product_edit_attributes'),
            'enable_family'    => $this->securityFacade->isGranted('pim_enrich_product_change_family'),
            'enable_state'     => $this->securityFacade->isGranted('pim_enrich_product_change_state'),
            'currentLocale'    => $this->getDataLocaleCode(),
            'comparisonLocale' => $this->getComparisonLocale(),
        ];
    }

    /**
     * Returns the options for the create form
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getCreateFormOptions(ProductInterface $product)
    {
        return [];
    }

    /**
     * Get the product edit template parameters
     *
     * @param FormInterface    $form
     * @param ProductInterface $product
     * @param array            $channels
     * @param array            $trees
     *
     * @return array
     */
    protected function getProductEditTemplateParams(
        FormInterface $form,
        ProductInterface $product,
        array $channels,
        array $trees
    ) {
        $sequentialEdit = $this->seqEditManager->findByUser($this->getUser());
        if ($sequentialEdit) {
            $this->seqEditManager->findWrap($sequentialEdit, $product);
        }

        $defaultParameters = [
            'form'             => $form->createView(),
            'dataLocale'       => $this->getDataLocaleCode(),
            'comparisonLocale' => $this->getComparisonLocale(),
            'channels'         => $channels,
            'attributesForm'   => $this->getAvailableAttributesForm($product->getAttributes())->createView(),
            'product'          => $product,
            'trees'            => $trees,
            'created'          => $this->versionManager->getOldestLogEntry($product),
            'updated'          => $this->versionManager->getNewestLogEntry($product),
            'locales'          => $this->getUserLocales(),
            'createPopin'      => $this->getRequest()->get('create_popin'),
            'sequentialEdit'   => $sequentialEdit
        ];

        $event = new GenericEvent($this, ['parameters' => $defaultParameters]);
        $this->dispatch(ProductEvents::PRE_RENDER_EDIT, $event);

        return $event->getArgument('parameters');
    }

    /**
     * Dispatch event if at least one listener is registered for it
     *
     * @param string $eventName
     * @param Event  $event
     */
    protected function dispatch($eventName, Event $event)
    {
        if ($this->eventDispatcher->hasListeners($eventName)) {
            $this->eventDispatcher->dispatch($eventName, $event);
        }
    }
}
