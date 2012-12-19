<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Form\Type\ProductType;
use APY\DataGridBundle\Grid\Action\RowAction;
use Pim\Bundle\UIBundle\Grid\Helper as GridHelper;
use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductToArrayTransformer;

/**
 * Product controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/product")
 */
class ProductController extends Controller
{

    /**
     * @return ProductManager
     */
    protected function getProductManager()
    {
        return $this->get('pim.catalog.product_manager');
        $this->get('translator')->trans('pim.catalog.attribute.title.list');

        $this->get('translator')->trans('unexistent translation');
    }

    /**
     * @return ProductTemplateManager
     */
    protected function getProductTemplateManager()
    {
        return $this->get('pim.catalog.product_template_manager');
    }

    /**
     * @return DocumentManager
     */
    protected function getPersistenceManager()
    {
        return $this->getProductManager()->getPersistenceManager();
    }

    /**
     * Lists all products
     *
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        // creates simple grid based on entity or document (ORM or ODM)
        $source = GridHelper::getGridSource($this->getPersistenceManager(), $this->getProductManager()->getEntityShortname());
        $grid = $this->get('grid');
        $grid->setSource($source);

        // add an action column
        $grid->setActionsColumnSeparator('&nbsp;');
        $rowAction = new RowAction('bap.action.edit', 'pim_catalog_product_edit', false, '_self', array('class' => 'grid_action ui-icon-fugue-document--pencil'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        return $grid->getGridResponse('PimCatalogBundle:Product:index.html.twig');
    }

    /**
     * Create product form
     *
     * @param ProductEntity $product product
     * @param array         $sets    sets array
     *
     * @return Form
     */
    protected function createProductForm($product, $sets = null)
    {
        $prodClassFullName = $this->getProductManager()->getEntityClass();
        $productType = new ProductType($prodClassFullName);
        // when create new product
        if ($sets) {
            $productType->setSetOptions($sets);
        }
        $form = $this->createForm($productType, $product);

        return $form;
    }

    /**
     * Displays a form to create a new attribute
     *
     * @Route("/new")
     * @Template()
     *
     * @return multitype
     */
    public function newAction()
    {
        $instance = $this->getProductManager()->getNewEntityInstance();
        $fromSets = $this->getProductTemplateManager()->getCopySetOptions();
        $form = $this->createProductForm($instance, $fromSets);

        // render form
        return $this->render(
            'PimCatalogBundle:Product:new.html.twig', array('entity' => $instance, 'form' => $form->createView())
        );
    }

    /**
     * Creates a new attribute
     *
     * @param Request $request the request
     *
     * @Route("/create")
     * @Method("POST")
     * @Template("PimCatalogBundle:ProductAttribute:edit.html.twig")
     *
     * @return multitype
     */
    public function createAction(Request $request)
    {
        // preapre new product
        $instance = $this->getProductManager()->getNewEntityInstance();
        $postData = $request->get('pim_catalogbundle_product');
        $sku = $postData['sku'];
        $instance->setSku($sku);
        // $setId = $postData['set'];
        // $set = $this->getProductTemplateManager()->getEntityRepository()->find($setId);
        // TODO : setup with default values ?

        // persist it
        try {
            $manager = $this->getPersistenceManager();
            $manager->persist($instance);
            $manager->flush();
            $this->get('session')->setFlash('success', "Product {$instance->getId()} has been created");

            return $this->redirect($this->generateUrl('pim_catalog_product_edit', array('id' => $instance->getId())));
        } catch (\Exception $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('pim_catalog_product_new', array()));
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @param integer $id the product id
     *
     * @Route("/{id}/edit")
     * @Template()
     *
     * @return multitype
     */
    public function editAction($id)
    {
        $entity = $this->getProductManager()->getEntityRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product.');
        }

        $form = $this->createProductForm($entity);

        // render form
        return $this->render(
            'PimCatalogBundle:Product:edit.html.twig',
            array('entity' => $entity, 'form' => $form->createView())
        );
    }

    /**
     * Clone a product and display edit form
     *
     * @param integer $id the product id to clone
     *
     * @Route("/{id}/clone")
     * @Template()
     *
     * @return multitype
     */
    public function cloneAction($id)
    {
        $entity = $this->getProductManager()->getEntityRepository()->find($id);

        // clone entity
        $cloneEntity = $this->getProductManager()->cloneEntity($entity);
        $manager = $this->getPersistenceManager();
        $manager->persist($cloneEntity);
        $manager->flush();
        $this->get('session')->setFlash('success', "Product {$cloneEntity->getId()} has been cloned from product {$entity->getId()}");

        return $this->redirect($this->generateUrl('pim_catalog_product_edit', array('id' => $cloneEntity->getId())));
    }

    /**
     * Edits an existing product entity.
     *
     * @param Request $request the request
     * @param integer $id      the product id
     *
     * @Route("/{id}/update")
     * @Method("POST")
     *
     * @return multitype
     */
    public function updateAction(Request $request, $id)
    {
        try {
            // transform posted data to understandable data
            $postData = $request->get('pim_catalogbundle_product');
            $productData = array();
            $productData['id']= $postData['id'];
            unset($postData['id']);
            $productData['sku']= $postData['sku'];
            unset($postData['_token']);
            $productData['values']= array();
            $productData['values'] = $postData;

            // transform array to set
            $transformer = new ProductToArrayTransformer($this->getProductManager());
            $instance = $transformer->reverseTransform($productData);

            // persist and flush set
            $manager = $this->getPersistenceManager();
            $manager->persist($instance);
            $manager->flush();
            $this->get('session')->setFlash('success', "Product {$instance->getId()} has been updated");

            return $this->redirect($this->generateUrl('pim_catalog_product_edit', array('id' => $instance->getId())));
        } catch (\Exception $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('pim_catalog_product_edit', array('id' => $instance->getId())));
    }

}
