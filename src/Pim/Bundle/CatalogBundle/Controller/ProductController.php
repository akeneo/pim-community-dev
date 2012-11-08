<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Entity\ProductField;
use Pim\Bundle\CatalogBundle\Document\ProductFieldMongo;
use Pim\Bundle\CatalogBundle\Form\Type\ProductType;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Product controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/product")
 */
class ProductController extends AbstractProductController
{

    /**
     * (non-PHPdoc)
     * @see Parent
     */
    public function getObjectShortName()
    {
        return $this->get('pim.catalog.product_manager')->getEntityShortname();
    }

    /**
     * Lists all fields
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        // creates simple grid based on entity or document (ORM or ODM)
        $source = $this->getGridSource();
        $grid = $this->get('grid');
        $grid->setSource($source);

        // add an action column
        $rowAction = new RowAction('Edit', 'pim_catalog_product_edit');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        return $grid->getGridResponse('PimCatalogBundle:Product:index.html.twig');
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit")
     * @Template()
     */
    public function editAction($id)
    {
        $manager = $this->getObjectManagerService();
        $entity = $this->get('pim.catalog.product_manager')->getEntityRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product.');
        }

        $classFullName = $this->getObjectClassFullName();
        $editForm = $this->createForm(new ProductType($classFullName), $entity);

        $params = array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        );

        // render form
        return $this->render('PimCatalogBundle:Product:edit.html.twig', $params);
    }

    /**
    * Edits an existing product entity.
     *
    * @Route("/{id}/update")
    * @Method("POST")
    */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->getObjectManagerService();

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product.');
        }

        $classFullName = $this->getObjectClassFullName();
        $editForm = $this->createForm(new ProductType($classFullName), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {

            // TODO : add some constraints in form

            $postData = $request->get('pim_catalogbundle_product');
            foreach ($postData as $fieldCode => $fieldValue) {
                $entity->setValue($fieldCode, $fieldValue);
            }

            $manager->persist($entity);
            $manager->flush();

            $this->get('session')->setFlash('success', 'Product has been updated');

            return $this->redirect($this->generateUrl('pim_catalog_product_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

}
