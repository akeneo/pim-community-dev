<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\Classification\Factory\CategoryFactory;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Manager\SequentialEditManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Enrich\Model\AvailableAttributes;
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
class ProductController
{
    const BACK_TO_GRID = 'BackGrid';

    const CREATE = 'Create';

    const SAVE_AND_NEXT = 'SaveAndNext';

    const SAVE_AND_FINISH = 'SaveAndFinish';

    /** @var Request */
    protected $request;

    /** @var EngineInterface */
    protected $templating;

    /** @var RouterInterface */
    protected $router;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** var ValidatorInterface */
    protected $validator;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

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

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param Request                     $request
     * @param EngineInterface             $templating
     * @param RouterInterface             $router
     * @param TokenStorageInterface       $tokenStorage
     * @param FormFactoryInterface        $formFactory
     * @param ValidatorInterface          $validator
     * @param TranslatorInterface         $translator
     * @param EventDispatcherInterface    $eventDispatcher
     * @param ProductRepositoryInterface  $productRepository
     * @param CategoryManager             $categoryManager
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UserContext                 $userContext
     * @param VersionManager              $versionManager
     * @param SecurityFacade              $securityFacade
     * @param ProductCategoryManager      $prodCatManager
     * @param SaverInterface              $productSaver
     * @param SequentialEditManager       $seqEditManager
     * @param ProductBuilderInterface     $productBuilder
     * @param CategoryFactory             $categoryFactory
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
        ProductRepositoryInterface $productRepository,
        CategoryManager $categoryManager,
        CategoryRepositoryInterface $categoryRepository,
        UserContext $userContext,
        VersionManager $versionManager,
        SecurityFacade $securityFacade,
        ProductCategoryManager $prodCatManager,
        SaverInterface $productSaver,
        SequentialEditManager $seqEditManager,
        ProductBuilderInterface $productBuilder,
        CategoryFactory $categoryFactory
    ) {
        $this->request            = $request;
        $this->templating         = $templating;
        $this->router             = $router;
        $this->tokenStorage       = $tokenStorage;
        $this->formFactory        = $formFactory;
        $this->validator          = $validator;
        $this->translator         = $translator;
        $this->productRepository  = $productRepository;
        $this->categoryManager    = $categoryManager;
        $this->userContext        = $userContext;
        $this->versionManager     = $versionManager;
        $this->securityFacade     = $securityFacade;
        $this->productCatManager  = $prodCatManager;
        $this->productSaver       = $productSaver;
        $this->seqEditManager     = $seqEditManager;
        $this->productBuilder     = $productBuilder;
        $this->categoryFactory    = $categoryFactory;
        $this->eventDispatcher    = $eventDispatcher;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * List products
     *
     * @AclAncestor("pim_enrich_product_index")
     * @Template
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->seqEditManager->removeByUser($this->tokenStorage->getToken()->getUser());

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
        $form    = $this->formFactory->create('pim_product_create', $product, $this->getCreateFormOptions($product));
        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->productSaver->save($product);
                $this->request->getSession()->getFlashBag()->add('success', new Message('flash.product.created'));

                if ($dataLocale === null) {
                    $dataLocale = $this->getDataLocaleCode();
                }

                $url = $this->router->generate(
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
        switch ($this->request->get('action')) {
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
     * @param int $id
     *
     * @AclAncestor("pim_enrich_product_history")
     *
     * @return Response
     */
    public function historyAction($id)
    {
        return $this->templating->renderResponse(
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
     * @Template("PimEnrichBundle:Product:listCategories.json.twig")
     * @AclAncestor("pim_enrich_product_categories_view")
     *
     * @return array
     */
    public function listCategoriesAction(Request $request, $id, $categoryId)
    {
        $product = $this->findProductOr404($id);
        $parent = $this->categoryRepository->find($categoryId);

        if (null === $parent) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $this->categoryFactory->getCategoryClass()));
        }

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
    protected function redirectToRoute($route, $parameters = [])
    {
        if (!isset($parameters['dataLocale'])) {
            $parameters['dataLocale'] = $this->getDataLocaleCode();
        }

        return new RedirectResponse($this->router->generate($route, $parameters));
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
        $locale = $this->request->query->get('compareWith');

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
        return $this->formFactory->create(
            'pim_available_attributes',
            $availableAttributes ?: new AvailableAttributes(),
            ['excluded_attributes' => $attributes]
        );
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
        $sequentialEdit = $this->seqEditManager->findByUser($this->tokenStorage->getToken()->getUser());
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
            'createPopin'      => $this->request->get('create_popin'),
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
