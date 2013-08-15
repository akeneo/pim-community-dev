<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;
use Pim\Bundle\ProductBundle\Entity\Category;
use Pim\Bundle\ProductBundle\Helper\CategoryHelper;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/product")
 *
 */
class ProductController extends Controller
{

    const CATEGORY_PREFIX = "category_node_";
    const TREE_APPLY_PREFIX = "apply_on_tree_";

    /**
     * List product attributes
     *
     * @param Request $request the request
     *
     * @Route("/.{_format}",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @return template
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager ProductDatagridManager */
        $gridManager = $this->get('pim_product.datagrid.manager.product');
        $gridManager->setFilterTreeId($this->getRequest()->get('treeId', 0));
        $gridManager->setFilterCategoryId($this->getRequest()->get('categoryId', 0));
        $datagrid = $gridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimProductBundle:Product:index.html.twig';
        }

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
     * @param string $dataLocale data locale
     *
     * @Route("/create/{dataLocale}", defaults={"dataLocale" = null})
     * @Template("PimProductBundle:Product:create.html.twig")
     *
     * @return array
     */
    public function createAction($dataLocale)
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_product_product_index');
        }

        $entity = $this->getProductManager()->createFlexible(true);

        if ($this->get('pim_product.form.handler.product_create')->process($entity)) {
            $this->addFlash('success', 'Product successfully saved.');

            if ($dataLocale === null) {
                $dataLocale = $this->getDataLocale();
            }
            $url = $this->generateUrl(
                'pim_product_product_edit',
                array('id' => $entity->getId(), 'dataLocale' => $dataLocale)
            );
            $response = array('status' => 1, 'url' => $url);

            return new Response(json_encode($response));
        }

        return array(
            'form'       => $this->get('pim_product.form.product_create')->createView(),
            'dataLocale' => $this->getDataLocale()
        );
    }

    /**
     * Edit product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Route(
     *     "/{id}/edit",
     *     requirements={"id"="\d+"}
     * )
     * @Template("PimProductBundle:Product:edit.html.twig")
     *
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $product  = $this->findProductOr404($id);
        $datagrid = $this->getDataAuditDatagrid(
            $product,
            'pim_product_product_edit',
            array(
                'id' => $product->getId()
            )
        );

        if ('json' === $request->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagrid->createView());
        }

        $channels = $this->getRepository('PimConfigBundle:Channel')->findAll();
        $trees    = $this->getCategoryManager()->getEntityRepository()->getProductsCountByTree($product);

        $form     = $this->createForm(
            'pim_product',
            $product,
            array('currentLocale' => $this->getDataLocale())
        );

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $categoriesData = $this->getCategoriesData($request->request->all());
                $categories = $this->getCategoryManager()->getCategoriesByIds($categoriesData['categories']);

                $this->getProductManager()->save($product, $categories, $categoriesData['trees']);

                $this->addFlash('success', 'Product successfully saved');

                // TODO : Check if the locale exists and is activated
                $params = array('id' => $product->getId(), 'dataLocale' => $this->getDataLocale());

                return $this->redirectToRoute('pim_product_product_edit', $params);
            } else {
                $this->addFlash('error', 'Please check your entry and try again.');
            }
        }

        $auditManager = $this->container->get('pim_product.manager.audit');

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
            'locales'        => $this->getLocaleManager()->getActiveCodes()
        );
    }

    /**
     * Add attributes to product
     *
     * @param int $id The product id to which add attributes
     *
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/{id}/attributes", requirements={"id"="\d+", "_method"="POST"})
     *
     */
    public function addProductAttributesAction($id)
    {
        $product             = $this->findProductOr404($id);
        $manager             = $this->getProductManager();
        $availableAttributes = new AvailableProductAttributes;
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $product->getAttributes(),
            $availableAttributes
        );
        $attributesForm->bind($this->getRequest());

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $manager->addAttributeToProduct($product, $attribute);
        }

        $manager->save($product);

        $this->addFlash('success', 'Attributes are added to the product form.');

        return $this->redirectToRoute('pim_product_product_edit', array('id' => $product->getId()));
    }

    /**
     * Remove product
     *
     * @param integer $id Id of the product to remove
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     * @Method("DELETE")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($id)
    {
        $product = $this->findProductOr404($id);
        $this->remove($product);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_product_product_index');
        }
    }

    /**
     * Remove an attribute form a product
     *
     * @param int $productId
     * @param int $attributeId
     *
     * @Route("/{productId}/attribute/{attributeId}/remove")
     * @Method("DELETE")
     * @return array
     */
    public function removeProductAttributeAction($productId, $attributeId)
    {
        $product   = $this->findOr404('PimProductBundle:Product', $productId);
        $attribute = $this->findOr404('PimProductBundle:ProductAttribute', $attributeId);

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

        return $this->redirectToRoute('pim_product_product_edit', array('id' => $productId));
    }

    /**
     * List categories associated with the provided product and descending from the category
     * defined by the parent parameter.
     *
     * @param integer  $id     Product id
     * @param Category $parent The parent category
     *
     * httpparam include_category if true, will include the parentCategory in the response
     *
     * @Route("/list-categories/product/{id}/parent/{category_id}.{_format}",
     *        requirements={"id"="\d+", "category_id"="\d+", "_format"="json"})
     * @ParamConverter("parent", class="PimProductBundle:Category", options={"id" = "category_id"})
     * @Template()
     *
     * @return array
     */
    public function listCategoriesAction($id, Category $parent)
    {
        $product = $this->findProductOr404($id);
        $categories = null;

        $includeParent = $this->getRequest()->get('include_parent', false);
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
        $manager = $this->container->get('pim_product.manager.product');
        $manager->setLocale($this->getDataLocale());

        return $manager;
    }

    /**
     * Get category tree manager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->container->get('pim_product.manager.category');
    }

    /**
     * Get locale manager
     *
     * @return \Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected function getLocaleManager()
    {
        return $this->container->get('pim_config.manager.locale');
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
     * @param int $id the product id
     *
     * @return Pim\Bundle\ProductBundle\Model\ProductInterface
     *
     * @throw Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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

        $currencyManager = $this->container->get('pim_config.manager.currency');
        $this->getProductManager()->addMissingPrices($currencyManager, $product);

        return $product;
    }
}
