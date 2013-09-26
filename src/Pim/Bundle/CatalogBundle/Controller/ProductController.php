<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\GridBundle\Renderer\GridRenderer;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Acl\Manager as AclManager;

use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Form\Handler\ProductCreateHandler;
use Pim\Bundle\CatalogBundle\Calculator\CompletenessCalculator;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;
use Pim\Bundle\CatalogBundle\Form\Type\AvailableProductAttributesType;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Helper\CategoryHelper;
use Pim\Bundle\CatalogBundle\Datagrid\ProductDatagridManager;
use Pim\Bundle\ImportExportBundle\Normalizer\FlatProductNormalizer;
use Pim\Bundle\VersioningBundle\Manager\AuditManager;
use Pim\Bundle\CatalogBundle\Exception\DeleteException;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_catalog_product",
 *      name="Product manipulation",
 *      description="Product manipulation",
 *      parent="pim_catalog"
 * )
 */
class ProductController extends AbstractDoctrineController
{
    /**
     * @var string
     */
    const CATEGORY_PREFIX = "category_node_";

    /**
     * @var string
     */
    const TREE_APPLY_PREFIX = "apply_on_tree_";

    /**
     * @var GridRenderer
     */
    private $gridRenderer;

    /**
     * @var DatagridWorkerInterface
     */
    private $datagridWorker;

    /**
     * @var ProductCreateHandler
     */
    private $productCreateHandler;

    /**
     * @var Form
     */
    private $productCreateForm;

    /**
     * @var CompletenessCalculator
     */
    private $calculator;

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
     * @var AclManager
     */
    private $aclManager;

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
     * @param GridRenderer             $gridRenderer
     * @param DatagridWorkerInterface  $datagridWorker
     * @param ProductCreateHandler     $productCreateHandler
     * @param Form                     $productCreateForm
     * @param CompletenessCalculator   $calculator
     * @param ProductManager           $productManager
     * @param CategoryManager          $categoryManager
     * @param LocaleManager            $localeManager
     * @param AuditManager             $auditManager
     * @param AclManager               $aclManager
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
        GridRenderer $gridRenderer,
        DatagridWorkerInterface $datagridWorker,
        ProductCreateHandler $productCreateHandler,
        Form $productCreateForm,
        CompletenessCalculator $calculator,
        ProductManager $productManager,
        CategoryManager $categoryManager,
        LocaleManager $localeManager,
        AuditManager $auditManager,
        AclManager $aclManager
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

        $this->gridRenderer         = $gridRenderer;
        $this->datagridWorker       = $datagridWorker;
        $this->productCreateHandler = $productCreateHandler;
        $this->productCreateForm    = $productCreateForm;
        $this->calculator           = $calculator;
        $this->productManager       = $productManager;
        $this->categoryManager      = $categoryManager;
        $this->localeManager        = $localeManager;
        $this->auditManager         = $auditManager;
        $this->aclManager           = $aclManager;

        $this->productManager->setLocale($this->getDataLocale());
    }
    /**
     * List product attributes
     *
     * @param Request $request the request
     *
     * @Acl(
     *      id="pim_catalog_product_index",
     *      name="View product list",
     *      description="View product list",
     *      parent="pim_catalog_product"
     * )
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager ProductDatagridManager */
        $gridManager = $this->datagridWorker->getDatagridManager('product');
        $gridManager->setFilterTreeId($request->get('treeId', 0));
        $gridManager->setFilterCategoryId($request->get('categoryId', 0));
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
                $response->setCallback($this->quickExportCallback($gridManager, $scope, static::BATCH_SIZE));

                return $response->send();

                break;
            case 'html':
            default:
                $view = 'PimCatalogBundle:Product:index.html.twig';
                break;
        }

        $params = array(
            'datagrid'   => $datagrid->createView(),
            'locales'    => $this->localeManager->getActiveLocales(),
            'dataLocale' => $this->getDataLocale(),
            'dataScope' => $this->getDataScope(),
        );

        return $this->render($view, $params);
    }

    /**
     * Quick export callback
     *
     * @param ProductDatagridManager $gridManager
     * @param string                 $scope
     * @param integer                $limit
     *
     * @return \Closure
     */
    protected function quickExportCallback(ProductDatagridManager $gridManager, $scope, $limit)
    {
        return function () use ($gridManager, $scope, $limit) {
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
                'scope' => $scope,
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
     * Return a response
     *
     * @param string  $content
     * @param integer $status
     * @param array   $headers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function returnResponse($content, $status = 200, $headers = array())
    {
        return new Response($content, $status, $headers);
    }

    /**
     * Create product
     *
     * @param Request $request
     * @param string  $dataLocale
     *
     * @Template
     * @Acl(
     *      id="pim_catalog_product_create",
     *      name="Create a product",
     *      description="Create a product",
     *      parent="pim_catalog_product"
     * )
     * @return array
     */
    public function createAction(Request $request, $dataLocale)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }

        $entity = $this->productManager->createFlexible(true);

        if ($this->productCreateHandler->process($entity)) {

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

        return array(
            'form'       => $this->productCreateForm->createView(),
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
     * @Acl(
     *      id="pim_catalog_product_edit",
     *      name="Edit a product",
     *      description="Edit a product",
     *      parent="pim_catalog_product"
     * )
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $product  = $this->findProductOr404($id);

        $datagrid = $this->datagridWorker->getDataAuditDatagrid(
            $product,
            'pim_catalog_product_edit',
            array(
                'id' => $product->getId()
            )
        );

        if ('json' === $request->getRequestFormat()) {
            return $this->gridRenderer->renderResultsJsonResponse($datagrid->createView());
        }

        $channels = $this->getRepository('PimCatalogBundle:Channel')->findAll();
        $trees    = $this->categoryManager->getEntityRepository()->getProductsCountByTree($product);

        $form     = $this->createForm(
            'pim_product',
            $product,
            array('currentLocale' => $this->getDataLocale())
        );

        if (!$this->aclManager->isResourceGranted('pim_catalog_product_change_family')) {
            $form->remove('family');
        }

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {

                $categoriesData = $this->getCategoriesData($request->request->all());
                $categories = $this->categoryManager->getCategoriesByIds($categoriesData['categories']);

                $this->productManager->handleMedia($product);
                $this->productManager->save($product, $categories, $categoriesData['trees']);
                // Call completeness calculator after validating data and saving product
                // so all values for all locale are loaded now
                $this->calculator->calculateForAProduct($product);
                $this->productManager->save($product);

                $this->addFlash('success', 'flash.product.updated');

                // TODO : Check if the locale exists and is activated
                $params = array('id' => $product->getId(), 'dataLocale' => $this->getDataLocale());

                return $this->redirectToRoute('pim_catalog_product_edit', $params);
            } else {
                $this->addFlash('error', 'flash.product.invalid');
            }
        }

        return array(
            'form'           => $form->createView(),
            'dataLocale'     => $this->getDataLocale(),
            'channels'       => $channels,
            'attributesForm' => $this->getAvailableProductAttributesForm($product->getAttributes())->createView(),
            'product'        => $product,
            'trees'          => $trees,
            'created'        => $this->auditManager->getOldestLogEntry($product),
            'updated'        => $this->auditManager->getNewestLogEntry($product),
            'datagrid'       => $datagrid->createView(),
            'locales'        => $this->localeManager->getActiveLocales()
        );
    }

    /**
     * Add attributes to product
     *
     * @param Request $request The request object
     * @param integer $id      The product id to which add attributes
     *
     * @Acl(
     *      id="pim_catalog_product_add_attribute",
     *      name="Add an attribute to a product",
     *      description="Add an attribute to a product",
     *      parent="pim_catalog_product"
     * )
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
        $attributesForm->bind($request);

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
     * @Acl(
     *      id="pim_catalog_product_remove",
     *      name="Remove a product",
     *      description="Remove a product",
     *      parent="pim_catalog_product"
     * )
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
     * @Acl(
     *      id="pim_catalog_product_remove_attribute",
     *      name="Remove a product's attribute",
     *      description="Remove a product's attribute",
     *      parent="pim_catalog_product"
     * )
     * @return RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function removeProductAttributeAction($productId, $attributeId)
    {
        $product   = $this->findOr404('PimCatalogBundle:Product', $productId);
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
     * @Acl(
     *      id="pim_catalog_product_categories_view",
     *      name="Consult the categories of a product",
     *      description="Consult the categories of a product",
     *      parent="pim_catalog_product"
     * )
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

        $treesData = CategoryHelper::listCategoriesResponse($trees, $categories);

        return array('trees' => $treesData);
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
     * Generate an array composed of an array of categories ids
     * from category_id_* params and an array of tree ids from
     * apply_to_tree_* params
     *
     * @param array $requestParameters
     *
     * @return array of categories data structured of two arrays
     *      categories, trees
     */
    protected function getCategoriesData(array $requestParameters)
    {
        $categories = array();
        $trees = array();

        foreach ($requestParameters as $key => $value) {
            if ($value === "1") {
                if (strpos($key, static::CATEGORY_PREFIX) === 0) {
                    $catId = (int) str_replace(static::CATEGORY_PREFIX, '', $key);
                    if ($catId > 0) {
                        $categories[] = $catId;
                    }
                } elseif (strpos($key, static::TREE_APPLY_PREFIX) === 0) {
                    $treeId = (int) str_replace(static::TREE_APPLY_PREFIX, '', $key);
                    if ($treeId > 0) {
                        $trees[] = $treeId;
                    }
                }
            }
        }

        return array('categories' => $categories, "trees" => $trees);
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
            $dataLocale = (string) $this->getUser()->getValue('cataloglocale');
        }
        if (!$dataLocale) {
            throw new \Exception('User must have a catalog locale defined');
        }
        if (!$this->aclManager->isResourceGranted('pim_catalog_locale_'.$dataLocale)) {
            throw new \Exception(sprintf("User doesn't have access to the locale '%s'", $dataLocale));
        }

        return $dataLocale;
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

        $this->productManager->addMissingPrices($product);

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
            new AvailableProductAttributesType(),
            $availableAttributes ?: new AvailableProductAttributes(),
            array('attributes' => $attributes)
        );
    }
}
