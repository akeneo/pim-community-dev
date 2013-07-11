<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;
use Pim\Bundle\ProductBundle\Entity\Category;
use Pim\Bundle\ProductBundle\Manager\MediaManager;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use YsTools\BackUrlBundle\Annotation\BackUrl;
use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;
use Pim\Bundle\ConfigBundle\Manager\LocaleManager;
use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;
use Pim\Bundle\ProductBundle\Helper\CategoryHelper;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
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
        $this->getProductManager()->setLocale($this->getDataLocale());
        $this->getProductManager()->setScope($this->getDataScope());

        /** @var $gridManager ProductDatagridManager */
        $gridManager = $this->get('pim_product.datagrid.manager.product');
        $datagrid = $gridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimProductBundle:Product:index.html.twig';
        }

        return $this->render($view, array('datagrid' => $datagrid->createView()));
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
            return $this->redirect($this->generateUrl('pim_product_product_index'));
        }

        $entity = $this->getProductManager()->createFlexible(true);

        if ($this->get('pim_product.form.handler.product_create')->process($entity)) {
            $this->addFlash('success', 'Product successfully saved.');

            $dataLocale = $entity->getLocales()->first()->getCode();
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
     * @param integer $id the product id
     *
     * @Route(
     *     "/{id}/edit",
     *     requirements={"id"="\d+"}
     * )
     * @Template("PimProductBundle:Product:edit.html.twig")
     *
     * @return array
     */
    public function editAction($id)
    {
        $product  = $this->findProductOr404($id);
        $request  = $this->getRequest();
        $channels = $this->getChannelRepository()->findAll();
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

                return $this->redirect(
                    $this->generateUrl(
                        'pim_product_product_edit',
                        array(
                            'id'         => $product->getId(),
                            'dataLocale' => $this->getDataLocale(),
                        )
                    )
                );
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
        );
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
     * Add attributes to product
     *
     * @param int $id The product id to which add attributes
     *
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/{id}/attributes", requirements={"id"="\d+", "_method"="POST"})
     *
     */
    public function addProductAttributes($id)
    {
        $product             = $this->findProductOr404($id);
        $availableAttributes = new AvailableProductAttributes;
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $product->getAttributes(),
            $availableAttributes
        );
        $attributesForm->bind($this->getRequest());

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attribute);
            $product->addValue($value);
        }

        $this->getProductManager()->save($product);

        $this->addFlash('success', 'Attributes are added to the product form.');
        $parameters = array('id' => $product->getId(), 'dataLocale' => $this->getDataLocale());

        return $this->redirect($this->generateUrl('pim_product_product_edit', $parameters));
    }

    /**
     * Remove product
     *
     * @param integer $id Id of the product to remove
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($id)
    {
        $product  = $this->findProductOr404($id);

        $em = $this->getProductManager()->getStorageManager();
        $em->remove($product);
        $em->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_product_product_index'));
        }
    }

    /**
     * Remove an attribute value
     *
     * @param int $productId
     * @param int $attributeId
     *
     * @Route("/{productId}/attributes/{attributeId}")
     * @Method("DELETE")
     * @return array
     */
    public function removeProductValueAction($productId, $attributeId)
    {
        $values = $this->getProductValueRepository()->findBy(
            array(
                'entity'    => $productId,
                'attribute' => $attributeId,
            )
        );

        if (false === $this->checkValuesRemovability($values)) {
            throw $this->createNotFoundException(
                sprintf(
                    'Could not find removable product attribute for product %d with id %d',
                    $productId,
                    $attributeId
                )
            );
        }

        $em = $this->getEntityManager();
        foreach ($values as $value) {
            $em->remove($value);
        }
        $em->flush();

        $this->addFlash('success', 'Attribute was successfully removed.');

        return $this->redirect($this->generateUrl('pim_product_product_edit', array('id' => $productId)));
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
     * Get product manager
     *
     * @return ProductManager
     */
    protected function getProductManager()
    {
        $pm = $this->container->get('pim_product.manager.product');
        $pm->setLocale($this->getDataLocale());

        return $pm;
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
     * @return LocaleManager
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
     * Get the AttributeGroup entity repository
     *
     * @return Pim\Bundle\ProductBundle\Entity\Repository\AttributeGroupRepository
     */
    protected function getAttributeGroupRepository()
    {
        return $this
            ->getDoctrine()
            ->getRepository('PimProductBundle:AttributeGroup');
    }

    /**
     * Get the Channel entity repository
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getChannelRepository()
    {
        return $this->getDoctrine()->getRepository('PimConfigBundle:Channel');
    }

    /**
     * Get the Product Value repository
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getProductValueRepository()
    {
        return $this->getProductManager()->getFlexibleValueRepository();
    }

    /**
     * Get the container parameter value
     *
     * @param string $name the container parameter name
     *
     * @return string
     */
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
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

        $localeCode = $this->getProductManager()->getLocale();
        if ($product->isEnabledForLocale($localeCode) === false) {
            throw $this->createNotFoundException(
                sprintf('Product with id %d is not enabled for locale %s', $id, $localeCode)
            );
        }

        $currencyManager = $this->container->get('pim_config.manager.currency');
        $this->getProductManager()->addMissingPrices($currencyManager, $product);

        return $product;
    }

    /**
     * Check if values can be removed
     *
     * @param array $values
     *
     * @return boolean
     */
    private function checkValuesRemovability(array $values)
    {
        if (0 === count($values)) {
            return false;
        }

        foreach ($values as $value) {
            if (!$value->isRemovable()) {
                return false;
            }
        }

        return true;
    }
}
