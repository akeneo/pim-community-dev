<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
// use Acme\Bundle\DemoFlexibleEntityBundle\Entity\Product;
use Acme\Bundle\DemoFlexibleEntityBundle\Form\Type\ProductType;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/product")
 */
class ProductController extends Controller
{

    /**
     * Get product manager
     * @return FlexibleManager
     */
    protected function getProductManager()
    {
        $pm = $this->container->get('product_manager');
        // force data locale if provided
        $dataLocale = $this->getRequest()->get('dataLocale');
        $pm->setLocale($dataLocale);
        // force data scope if provided
        $dataScope = $this->getRequest()->get('dataScope');
        $dataScope = ($dataScope) ? $dataScope : 'ecommerce';
        $pm->setScope($dataScope);

        return $pm;
    }

    /**
     * Get attribute codes
     * @return array
     */
    protected function getAttributeCodesToDisplay()
    {
        return array('name', 'description', 'size', 'color', 'price');
    }

    /**
     * Index action
     *
     * @param string $dataLocale locale
     * @param string $dataScope  scope
     *
     * @Route("/index/{dataLocale}/{dataScope}", defaults={"dataLocale" = null, "dataScope" = null})
     * @Template()
     *
     * @return array
     */
    public function indexAction($dataLocale, $dataScope)
    {
        $products = $this->getProductManager()->getFlexibleRepository()->findByWithAttributes();

        return array('products' => $products, 'attributes' => $this->getAttributeCodesToDisplay());
    }

    /**
     * Lazy load action
     * @param string $dataLocale locale
     * @param string $dataScope  scope
     *
     * @Route("/querylazyload/{dataLocale}/{dataScope}", defaults={"dataLocale" = null, "dataScope" = null})
     * @Template("AcmeDemoFlexibleEntityBundle:Product:index.html.twig")
     *
     * @return array
     */
    public function querylazyloadAction($dataLocale, $dataScope)
    {
        // get only entities, values and attributes are lazy loaded
        // you can use any criteria, order you want it's a classic doctrine query
        $products = $this->getProductManager()->getFlexibleRepository()->findBy(array());

        return array('products' => $products, 'attributes' => $this->getAttributeCodesToDisplay());
    }

    /**
     * Product query action
     *
     * @param string $dataLocale locale
     * @param string $dataScope  scope
     * @param string $attributes attribute codes
     * @param string $criteria   criterias
     * @param string $orderBy    order by
     * @param int    $limit      limit
     * @param int    $offset     offset
     *
     * @Route("/query/{dataLocale}/{dataScope}/{attributes}/{criteria}/{orderBy}/{limit}/{offset}",
     *         defaults={"dataLocale" = null, "dataScope" = null, "attributes" = null, "criteria" = null, "orderBy" = null, "limit" = null, "offset" = null})
     *
     * @Template("AcmeDemoFlexibleEntityBundle:Product:index.html.twig")
     *
     * @return array
     */
    public function queryAction($dataLocale, $dataScope, $attributes, $criteria, $orderBy, $limit, $offset)
    {
        // prepare params
        if (!is_null($attributes) and $attributes !== 'null') {
            $attributes = explode('&', $attributes);
        } else {
            $attributes = array();
        }
        if (!is_null($criteria) and $criteria !== 'null') {
            parse_str($criteria, $criteria);
        } else {
            $criteria = array();
        }
        if (!is_null($orderBy) and $orderBy !== 'null') {
            parse_str($orderBy, $orderBy);
        } else {
            $orderBy = array();
        }

        // get entities
        $products = $this->getProductManager()->getFlexibleRepository()->findByWithAttributes(
            $attributes, $criteria, $orderBy, $limit, $offset
        );

        return array('products' => $products, 'attributes' => $this->getAttributeCodesToDisplay());
    }

    /**
     * Show details
     *
     * @param integer $id         id
     * @param string  $dataLocale data locale
     * @param string  $dataScope  data scope
     *
     * @Route("/show/{id}/{dataLocale}/{dataScope}", defaults={"dataLocale" = null, "dataScope" = null})
     * @Template()
     *
     * @return array
     */
    public function showAction($id, $dataLocale, $dataScope)
    {
        // load with any values
        $product = $this->getProductManager()->getFlexibleRepository()->findWithAttributes($id);

        return array('product' => $product);
    }

    /**
     * Create product
     *
     * @param string $dataLocale data locale
     * @param string $dataScope  data scope
     *
     * @Route("/create/{dataLocale}/{dataScope}", defaults={"dataLocale" = null, "dataScope" = null})
     * @Template("AcmeDemoFlexibleEntityBundle:Product:edit.html.twig")
     *
     * @return array
     */
    public function createAction($dataLocale, $dataScope)
    {
        $entity = $this->getProductManager()->createFlexible(true);

        return $this->editAction($entity, $dataLocale, $dataScope);
    }

    /**
     * Edit product
     *
     * @param Product $entity     product
     * @param string  $dataLocale data locale
     * @param string  $dataScope  data scope
     *
     * @Route("/edit/{id}/{dataLocale}/{dataScope}", requirements={"id"="\d+"}, defaults={"id"=0, "dataLocale" = null, "dataScope" = null})
     * @Template
     *
     * @return array
     */
    public function editAction(Product $entity, $dataLocale, $dataScope)
    {
        $request = $this->getRequest();

        // create form
        $entClassName = $this->getProductManager()->getFlexibleName();
        $valueClassName = $this->getProductManager()->getFlexibleValueName();
        $form = $this->createForm(new ProductType($entClassName, $valueClassName), $entity);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getProductManager()->getStorageManager();
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Product successfully saved');

                return $this->redirect($this->generateUrl('acme_demoflexibleentity_product_index'));
            }
        }

        return array(
                'form' => $form->createView(),
        );
    }

    /**
     * Remove product
     *
     * @param Product $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     */
    public function removeAction(Product $entity)
    {
        $em = $this->getProductManager()->getStorageManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Product successfully removed');

        return $this->redirect($this->generateUrl('acme_demoflexibleentity_product_index'));
    }

}
