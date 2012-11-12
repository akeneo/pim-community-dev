<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Form\Type\ProductType;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;
use APY\DataGridBundle\Grid\Action\RowAction;
use Pim\Bundle\UIBundle\Grid\Helper as GridHelper;

/**
 * Product controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
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
     */
    public function indexAction()
    {
        // creates simple grid based on entity or document (ORM or ODM)
        $source = GridHelper::getGridSource($this->getPersistenceManager(), $this->getProductManager()->getEntityShortname());
        $grid = $this->get('grid');
        $grid->setSource($source);

        // add an action column
        $grid->setActionsColumnSeparator('&nbsp;');
        $rowAction = new RowAction('Details', 'pim_catalog_product_edit', false, '_self', array('class' => 'grid_action ui-icon-fugue-magnifier'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        return $grid->getGridResponse('PimCatalogBundle:Product:index.html.twig');
    }

    /**
     * Create product form
     *
     * @param ProductEntity $product
     * @return Form
     */
    protected function createProductForm($product)
    {
        $prodClassFullName = $this->getProductManager()->getEntityClass();
        $form = $this->createForm(new ProductType($prodClassFullName), $product);
        return $form;
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit")
     * @Template()
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
    * Edits an existing product entity.
     *
    * @Route("/{id}/update")
    * @Method("POST")
    */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->getProductManager()->getEntityRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product.');
        }

        $form = $this->createProductForm($entity);
        $form->bind($request);

        if ($form->isValid()) {

            // TODO : add some constraints in form

            //$postData = $request->get('pim_catalogbundle_product');
            /*
            foreach ($postData as $attributeCode => $attributeValue) {
                $entity->setValue($attributeCode, $attributeValue);
            }*/

            $manager = $this->getPersistenceManager();
            $manager->persist($entity);
            $manager->flush();

            $this->get('session')->setFlash('success', 'Product has been updated');

            return $this->redirect($this->generateUrl('pim_catalog_product_edit', array('id' => $id)));
        }

        // render form
        return $this->render(
            'PimCatalogBundle:Product:edit.html.twig',
            array('entity' => $entity, 'form' => $form->createView())
        );
    }

}
