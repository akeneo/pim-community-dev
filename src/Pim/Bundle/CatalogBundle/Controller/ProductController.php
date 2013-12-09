<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Pim\Bundle\CatalogBundle\Exception\MediaManagementException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\GridBundle\Helper\DatagridHelperInterface;
use Pim\Bundle\CatalogBundle\Datagrid\ProductDatagridManager;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Exception\DeleteException;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\ImportExportBundle\Normalizer\FlatProductNormalizer;
use Pim\Bundle\VersioningBundle\Manager\AuditManager;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController extends AbstractDoctrineController
{
    /**
     * @var DatagridHelperInterface
     */
    private $datagridHelper;

    /**
     * @var ProductManager
     */
    private $productManager;

    /**
     * @var CategoryManager
     */
    private $categoryManager;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var AuditManager
     */
    private $auditManager;

    /**
     * @var SecurityFacade
     */
    private $securityFacade;

    /**
     * @staticvar int
     */
    const BATCH_SIZE = 250;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param DatagridHelperInterface  $datagridHelper
     * @param ProductManager           $productManager
     * @param CategoryManager          $categoryManager
     * @param LocaleManager            $localeManager
     * @param AuditManager             $auditManager
     * @param SecurityFacade           $securityFacade
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        DatagridHelperInterface $datagridHelper,
        ProductManager $productManager,
        CategoryManager $categoryManager,
        LocaleManager $localeManager,
        AuditManager $auditManager,
        SecurityFacade $securityFacade
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->datagridHelper       = $datagridHelper;
        $this->productManager       = $productManager;
        $this->categoryManager      = $categoryManager;
        $this->localeManager        = $localeManager;
        $this->auditManager         = $auditManager;
        $this->securityFacade       = $securityFacade;

        $this->productManager->setLocale($this->getDataLocale());
    }
    /**
     * List product attributes
     *
     * @param Request $request the request
     *
     * @AclAncestor("pim_catalog_product_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager ProductDatagridManager */
        $gridManager = $this->datagridHelper->getDatagridManager('product');
        $gridManager->setFilterTreeId($request->get('treeId', 0));
        $gridManager->setFilterCategoryId($request->get('categoryId', 0));
        $gridManager->setIncludeSub($request->get('includeSub', 0));
        $datagrid = $gridManager->getDatagrid();

        switch ($request->getRequestFormat()) {
            case 'json':
                $view = 'OroGridBundle:Datagrid:list.json.php';
                break;
            case 'csv':
                // Export time execution depends on entities exported
                ignore_user_abort(false);
                set_time_limit(0);

                $scope = $this->productManager->getScope();

                $dateTime = new \DateTime();
                $fileName = sprintf(
                    'products_export_%s_%s_%s.csv',
                    $this->getDataLocale(),
                    $scope,
                    $dateTime->format('Y-m-d_H:i:s')
                );

                // prepare response
                $response = new StreamedResponse();
                $attachment = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $fileName);
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Disposition', $attachment);
                $response->setCallback($this->quickExportCallback($gridManager, static::BATCH_SIZE));

                return $response->send();

                break;
            case 'html':
            default:
                $view = 'PimCatalogBundle:Product:index.html.twig';
                break;
        }

        $params = array(
            'datagrid'   => $datagrid->createView(),
            'locales'    => $this->localeManager->getUserLocales(),
            'dataLocale' => $this->getDataLocale(),
            'dataScope'  => $this->getDataScope()
        );

        return $this->render($view, $params);
    }

    /**
     * Quick export callback
     *
     * @param ProductDatagridManager $gridManager
     * @param integer                $limit
     *
     * @return \Closure
     */
    protected function quickExportCallback(ProductDatagridManager $gridManager, $limit)
    {
        return function () use ($gridManager, $limit) {
            flush();

            $proxyQuery = $gridManager->getDatagrid()->getQueryWithParametersApplied();

            // get attribute lists
            $fieldsList = $gridManager->getAvailableAttributeCodes($proxyQuery);
            $fieldsList[] = FlatProductNormalizer::FIELD_FAMILY;
            $fieldsList[] = FlatProductNormalizer::FIELD_CATEGORY;

            // prepare serializer context
            $context = array(
                'withHeader' => true,
                'heterogeneous' => false,
                'fields' => $fieldsList
            );

            // prepare serializer batching
            $count = $gridManager->getDatagrid()->countResults();
            $iterations = ceil($count/$limit);

            $gridManager->prepareQueryForExport($proxyQuery, $fieldsList);

            for ($i=0; $i<$iterations; $i++) {
                $data = $gridManager->getDatagrid()->exportData($proxyQuery, 'csv', $context, $i*$limit, $limit);
                echo $data;
                flush();
            }
        };
    }

    /**
     * Create product
     *
     * @param Request $request
     * @param string  $dataLocale
     *
     * @Template
     * @AclAncestor("pim_catalog_product_create")
     * @return array
     */
    public function createAction(Request $request, $dataLocale)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }

        $entity = $this->productManager->createProduct();
        $form = $this->createForm('pim_product_create', $entity, $this->getCreateFormOptions($entity));
        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->productManager->save($entity);
                $this->addFlash('success', 'flash.product.created');

                if ($dataLocale === null) {
                    $dataLocale = $this->getDataLocale();
                }
                $url = $this->generateUrl(
                    'pim_catalog_product_edit',
                    array('id' => $entity->getId(), 'dataLocale' => $dataLocale)
                );
                $response = array('status' => 1, 'url' => $url);

                return new Response(json_encode($response));
            }
        }

        return array(
            'form'       => $form->createView(),
            'dataLocale' => $this->getDataLocale()
        );
    }

    /**
     * Edit product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pim_catalog_product_edit")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $this->productManager->ensureAllAssociations($product);

        $form = $this->createForm(
            'pim_product_edit',
            $product,
            $this->getEditFormOptions($product)
        );

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                try {
                    $this->productManager->handleMedia($product);
                    $this->productManager->save($product);

                    $this->addFlash('success', 'flash.product.updated');
                } catch (MediaManagementException $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                // TODO : Check if the locale exists and is activated
                $params = array('id' => $product->getId(), 'dataLocale' => $this->getDataLocale());
                if ($comparisonLocale = $this->getComparisonLocale()) {
                    $params['compareWith'] = $comparisonLocale;
                }

                return $this->redirectToRoute('pim_catalog_product_edit', $params);
            } else {
                $this->addFlash('error', 'flash.product.invalid');
            }
        }

        $channels = $this->getRepository('PimCatalogBundle:Channel')->findAll();
        $trees    = $this->categoryManager->getEntityRepository()->getProductsCountByTree($product);

        $associations = $this->getRepository('PimCatalogBundle:Association')->findAll();

        $productGrid = $this->datagridHelper->getDatagridManager('association_product');
        $productGrid->setProduct($product);

        $groupGrid = $this->datagridHelper->getDatagridManager('association_group');
        $groupGrid->setProduct($product);

        $association = null;
        if (!empty($associations)) {
            $association = reset($associations);
            $productGrid->setAssociationId($association->getId());
            $groupGrid->setAssociationId($association->getId());
        }

        $routeParameters = array('id' => $product->getId());
        $productGrid->getRouteGenerator()->setRouteParameters($routeParameters);
        $groupGrid->getRouteGenerator()->setRouteParameters($routeParameters);

        $productGridView = $productGrid->getDatagrid()->createView();
        $groupGridView   = $groupGrid->getDatagrid()->createView();

        return array(
            'form'                   => $form->createView(),
            'dataLocale'             => $this->getDataLocale(),
            'comparisonLocale'       => $this->getComparisonLocale(),
            'channels'               => $channels,
            'attributesForm'         =>
                $this->getAvailableProductAttributesForm($product->getAttributes())->createView(),
            'product'                => $product,
            'trees'                  => $trees,
            'created'                => $this->auditManager->getOldestLogEntry($product),
            'updated'                => $this->auditManager->getNewestLogEntry($product),
            'associations'           => $associations,
            'associationProductGrid' => $productGridView,
            'associationGroupGrid'   => $groupGridView,
            'locales'                => $this->localeManager->getUserLocales(),
        );
    }

    /**
     * History of a product
     *
     * @param Request $request
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function historyAction(Request $request, $id)
    {
        $product  = $this->findProductOr404($id);
        $historyGridView = $this->getHistoryGrid($product)->createView();

        if ('json' === $request->getRequestFormat()) {
            return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($historyGridView);
        } else {
            return $this->render(
                'PimCatalogBundle:Product:_history.html.twig',
                array(
                    'product'           => $product,
                    'historyDatagrid'   => $historyGridView
                )
            );
        }
    }

    /**
     * Add attributes to product
     *
     * @param Request $request The request object
     * @param integer $id      The product id to which add attributes
     *
     * @AclAncestor("pim_catalog_product_add_attribute")
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addProductAttributesAction(Request $request, $id)
    {
        $product             = $this->findProductOr404($id);
        $availableAttributes = new AvailableProductAttributes();
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $product->getAttributes(),
            $availableAttributes
        );
        $attributesForm->submit($request);

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $this->productManager->addAttributeToProduct($product, $attribute);
        }

        $this->productManager->save($product);

        $this->addFlash('success', 'flash.product.attributes added');

        return $this->redirectToRoute('pim_catalog_product_edit', array('id' => $product->getId()));
    }

    /**
     * Remove product
     *
     * @param Request $request
     * @param integer $id
     *
     * @AclAncestor("pim_catalog_product_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $this->getManager()->remove($product);
        $this->getManager()->flush();
        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_product_index');
        }
    }

    /**
     * Remove an attribute form a product
     *
     * @param integer $productId
     * @param integer $attributeId
     *
     * @AclAncestor("pim_catalog_product_remove_attribute")
     * @return RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function removeProductAttributeAction($productId, $attributeId)
    {
        $product   = $this->findOr404('Pim\Bundle\CatalogBundle\Model\Product', $productId);
        $attribute = $this->findOr404('PimCatalogBundle:ProductAttribute', $attributeId);

        if ($product->isAttributeRemovable($attribute)) {
            $this->productManager->removeAttributeFromProduct($product, $attribute);
        } else {
            throw new DeleteException($this->getTranslator()->trans('product.attribute not removable'));
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_product_edit', array('id' => $productId));
        }
    }

    /**
     * List categories associated with the provided product and descending from the category
     * defined by the parent parameter.
     *
     * @param Request  $request The request object
     * @param integer  $id      Product id
     * @param Category $parent  The parent category
     *
     * httpparam include_category if true, will include the parentCategory in the response
     *
     * @ParamConverter("parent", class="PimCatalogBundle:Category", options={"id" = "category_id"})
     * @Template
     * @AclAncestor("pim_catalog_product_categories_view")
     * @return array
     */
    public function listCategoriesAction(Request $request, $id, Category $parent)
    {
        $product = $this->findProductOr404($id);
        $categories = null;

        $includeParent = $request->get('include_parent', false);
        $includeParent = ($includeParent === 'true');

        if ($product != null) {
            $categories = $product->getCategories();
        }
        $trees = $this->categoryManager->getFilledTree($parent, $categories);

        return array('trees' => $trees, 'categories' => $categories);
    }

    /**
     * List product associations for the provided product
     *
     * @param Request $request The request object
     * @param integer $id      Product id
     *
     * @Template
     * @AclAncestor("pim_catalog_product_associations_view")
     * @return Response
     */
    public function listProductAssociationsAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $datagridManager = $this->datagridHelper->getDatagridManager('association_product');
        $datagridManager->setProduct($product);

        $datagridView = $datagridManager->getDatagrid()->createView();

        return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($datagridView);
    }

    /**
     * List group associations for the provided product
     *
     * @param Request $request The request object
     * @param integer $id      Product id
     *
     * @Template
     * @AclAncestor("pim_catalog_product_associations_view")
     * @return Response
     */
    public function listGroupAssociationsAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $datagridManager = $this->datagridHelper->getDatagridManager('association_group');
        $datagridManager->setProduct($product);

        $datagridView = $datagridManager->getDatagrid()->createView();

        return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($datagridView);
    }

    /**
     * {@inheritdoc}
     */
    protected function redirectToRoute($route, $parameters = array(), $status = 302)
    {
        if (!isset($parameters['dataLocale'])) {
            $parameters['dataLocale'] = $this->getDataLocale();
        }

        return parent::redirectToRoute($route, $parameters, $status);
    }

    /**
     * Get data locale code
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getDataLocale()
    {
        $dataLocale = $this->getRequest()->get('dataLocale');
        if ($dataLocale === null) {
            return 'en_US';
            // TODO : to fix
            //$dataLocale = (string) $this->getUser()->getValue('cataloglocale');
        }
        if (!$dataLocale) {
            throw new \Exception('User must have a catalog locale defined');
        }
        if (!$this->securityFacade->isGranted('pim_catalog_locale_'.$dataLocale)) {
            throw new \Exception(sprintf("User doesn't have access to the locale '%s'", $dataLocale));
        }

        return $dataLocale;
    }

    /**
     * @return string
     */
    protected function getComparisonLocale()
    {
        $locale = $this->getRequest()->query->get('compareWith');

        if ($this->getDataLocale() !== $locale) {
            return $locale;
        }
    }

    /**
     * Get data currency code
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getDataCurrency()
    {
        $dataLocaleCode = $this->getDataLocale();
        $dataLocale = $this->localeManager->getLocaleByCode($dataLocaleCode);

        return $dataLocale->getDefaultCurrency();
    }

    /**
     * Get data scope
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getDataScope()
    {
        $dataScope = $this->getRequest()->get('dataScope');
        if ($dataScope === null) {
            return 'ecommerce';

            // TODO : to fix
            $dataScope = (string) $this->getUser()->getValue('catalogscope');
        }
        if (!$dataScope) {
            throw new \Exception('User must have a catalog scope defined');
        }

        return $dataScope;
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param integer $id the product id
     *
     * @return Pim\Bundle\CatalogBundle\Model\ProductInterface
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function findProductOr404($id)
    {
        $product = $this->productManager->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                sprintf('Product with id %d could not be found.', $id)
            );
        }

        return $product;
    }

    /**
     * Get the AvailbleProductAttributes form
     *
     * @param array                      $attributes          The product attributes
     * @param AvailableProductAttributes $availableAttributes The available attributes container
     *
     * @return Symfony\Component\Form\Form
     */
    protected function getAvailableProductAttributesForm(
        array $attributes = array(),
        AvailableProductAttributes $availableAttributes = null
    ) {
        return $this->createForm(
            'pim_available_product_attributes',
            $availableAttributes ?: new AvailableProductAttributes(),
            array('attributes' => $attributes)
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
        return array(
            'enable_family'    => $this->securityFacade->isGranted('pim_catalog_product_change_family'),
            'enable_state'     => $this->securityFacade->isGranted('pim_catalog_product_change_state'),
            'currentLocale'    => $this->getDataLocale(),
            'comparisonLocale' => $this->getComparisonLocale(),
        );
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
        return array();
    }

    /**
     * @param ProductInterface $product
     *
     * @return Datagrid
     */
    protected function getHistoryGrid(ProductInterface $product)
    {
        $historyGrid = $this->datagridHelper->getDataAuditDatagrid(
            $product,
            'pim_catalog_product_history',
            array('id' => $product->getId())
        );

        return $historyGrid;
    }
}
