<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Helper\CategoryHelper;

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
class ProductController extends Controller
{
    const CATEGORY_PREFIX = "category_node_";
    const TREE_APPLY_PREFIX = "apply_on_tree_";

    /**
     * List product attributes
     *
     * @param Request $request the request
     * @Acl(
     *      id="pim_catalog_product_index",
     *      name="View product list",
     *      description="View product list",
     *      parent="pim_catalog_product"
     * )
     * @return template
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager ProductDatagridManager */
        $gridManager = $this->get('pim_catalog.datagrid.manager.product');
        $gridManager->setFilterTreeId($request->get('treeId', 0));
        $gridManager->setFilterCategoryId($request->get('categoryId', 0));
        $datagrid = $gridManager->getDatagrid();

        $view =  ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimCatalogBundle:Product:index.html.twig';

        $params = array(
            'datagrid'   => $datagrid->createView(),
            'locales'    => $this->getLocaleManager()->getActiveLocales(),
            'dataLocale' => $this->getDataLocale(),
            'dataScope' => $this->getDataScope(),
        );

        return $this->render($view, $params);
    }

    /**
     * Create product
     *
     * @param Request $request
     * @param string  $dataLocale
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

        $entity = $this->getProductManager()->createFlexible(true);

        if ($this->get('pim_catalog.form.handler.product_create')->process($entity)) {

            $pendingManager = $this->container->get('pim_versioning.manager.pending');
            if ($pending = $pendingManager->getPendingVersion($entity)) {
                $pendingManager->createVersionAndAudit($pending);
            }

            $this->addFlash('success', 'Product successfully saved.');

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
            'form'       => $this->get('pim_catalog.form.product_create')->createView(),
            'dataLocale' => $this->getDataLocale()
        );
    }

    /**
     * Edit product
     *
     * @param Request $request
     * @param integer $id
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

        $datagrid = $this->getDataAuditDatagrid(
            $product,
            'pim_catalog_product_edit',
            array(
                'id' => $product->getId()
            )
        );

        if ('json' === $request->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagrid->createView());
        }

        $channels = $this->getRepository('PimCatalogBundle:Channel')->findAll();
        $trees    = $this->getCategoryManager()->getEntityRepository()->getProductsCountByTree($product);

        $form     = $this->createForm(
            'pim_product',
            $product,
            array('currentLocale' => $this->getDataLocale())
        );

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                // Call completeness calculator after validating data
                $calculator = $this->container->get('pim_catalog.calculator.completeness');
                $calculator->calculateForAProduct($product);

                $categoriesData = $this->getCategoriesData($request->request->all());
                $categories = $this->getCategoryManager()->getCategoriesByIds($categoriesData['categories']);

                $this->getProductManager()->save($product, $categories, $categoriesData['trees']);

                $this->addFlash('success', 'Product successfully saved');

                $pendingManager = $this->container->get('pim_versioning.manager.pending');
                if ($pending = $pendingManager->getPendingVersion($product)) {
                    $pendingManager->createVersionAndAudit($pending);
                }

                // TODO : Check if the locale exists and is activated
                $params = array('id' => $product->getId(), 'dataLocale' => $this->getDataLocale());

                return $this->redirectToRoute('pim_catalog_product_edit', $params);
            } else {
                $this->addFlash('error', 'Please check your entry and try again.');
            }
        }

        $auditManager = $this->container->get('pim_versioning.manager.audit');

        return array(
            'form'           => $form->createView(),
            'dataLocale'     => $this->getDataLocale(),
            'channels'       => $channels,
            'attributesForm' => $this->getAvailableProductAttributesForm($product->getAttributes())->createView(),
            'product'        => $product,
            'trees'          => $trees,
            'created'        => $auditManager->getFirstLogEntry($product),
            'updated'        => $auditManager->getLastLogEntry($product),
            'datagrid'       => $datagrid->createView(),
            'locales'        => $this->getLocaleManager()->getActiveLocales()
        );
    }

    /**
     * Add attributes to product
     *
     * @param Request $request The request object
     * @param integer $id      The product id to which add attributes
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
        $manager             = $this->getProductManager();
        $availableAttributes = new AvailableProductAttributes();
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $product->getAttributes(),
            $availableAttributes
        );
        $attributesForm->bind($request);

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $manager->addAttributeToProduct($product, $attribute);
        }

        $manager->save($product);

        $this->addFlash('success', 'Attributes are added to the product form.');

        return $this->redirectToRoute('pim_catalog_product_edit', array('id' => $product->getId()));
    }

    /**
     * Remove product
     *
     * @param Request $request
     * @param integer $id
     * @Acl(
     *      id="pim_catalog_product_remove",
     *      name="Remove a product",
     *      description="Remove a product",
     *      parent="pim_catalog_product"
     * )
     * @return Response
     */
    public function removeAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $this->remove($product);

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
     * @Acl(
     *      id="pim_catalog_product_remove_attribute",
     *      name="Remove a product's attribute",
     *      description="Remove a product's attribute",
     *      parent="pim_catalog_product"
     * )
     * @return array
     */
    public function removeProductAttributeAction($productId, $attributeId)
    {
        $product   = $this->findOr404('PimCatalogBundle:Product', $productId);
        $attribute = $this->findOr404('PimCatalogBundle:ProductAttribute', $attributeId);

        if (!$product->isAttributeRemovable($attribute)) {
            throw $this->createNotFoundException(
                sprintf(
                    'Attribute %s can not be removed from the product %s',
                    $attribute->getCode(),
                    $product->getCode()
                )
            );
        }

        $this->getProductManager()->removeAttributeFromProduct($product, $attribute);

        $this->addFlash('success', 'Attribute was successfully removed.');

        return $this->redirectToRoute('pim_catalog_product_edit', array('id' => $productId));
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
        $trees = $this->getCategoryManager()->getFilledTree($parent, $categories);

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
     * Get product manager
     *
     * @return ProductManager
     */
    protected function getProductManager()
    {
        $manager = $this->container->get('pim_catalog.manager.product');
        $manager->setLocale($this->getDataLocale());

        return $manager;
    }

    /**
     * Get category tree manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->container->get('pim_catalog.manager.category');
    }

    /**
     * Get locale manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManager()
    {
        return $this->container->get('pim_catalog.manager.locale');
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

        if (!$this->container->get('oro_user.acl_manager')->isResourceGranted('pim_catalog_locale_'.$dataLocale)) {
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
        $dataLocale = $this->getLocaleManager()->getLocaleByCode($dataLocaleCode);

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
        $product = $this->getProductManager()->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                sprintf('Product with id %d could not be found.', $id)
            );
        }

        // TODO : Maybe just check if the locale is well activated

        $this->getProductManager()->addMissingPrices($product);

        return $product;
    }
}
