<?php
namespace Akeneo\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction as GridRowAction;
use Akeneo\CatalogBundle\Entity\Product\Field;
use Akeneo\CatalogBundle\Form\Product\FieldType;

/**
 * Field controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FieldController extends Controller
{
    /**
     * Get full qualified class name for field entity
     * @return string
     */
    protected function _getFieldFQCN()
    {
        // TODO we can't use short name as AkeneoCatalogBundle:Field because
        // of subdirectory in entity directory
        return 'Akeneo\CatalogBundle\Entity\Product\Field';
    }

    /**
     * @Route("/field/index")
     * @Template()
     */
    public function indexAction()
    {
        // creates simple grid based on ORM entity
        $source = new GridEntity($this->_getFieldFQCN());
        $grid = $this->get('grid');
        $grid->setSource($source);
        // add an action column
         $rowAction = new GridRowAction('Edit', 'akeneo_catalog_field_edit');
         $rowAction->setRouteParameters(array('id'));
         $grid->addRowAction($rowAction);
        // manage the grid redirection and exports response of the controller
        return $grid->getGridResponse('AkeneoCatalogBundle:Field:index.html.twig');
    }

    /**
     * @Route("/field/new")
     * @Template()
     */
    public function newAction()
    {
        // create a new field and prepare field form rendering
        $field = new Field();
        $form = $this->createForm(new FieldType(), $field);
        return $this->render(
            'AkeneoCatalogBundle:Field:edit.html.twig', array('form' => $form->createView(),)
        );
    }

    /**
     * @Route("/field/edit/{id}")
     * @Template()
     */
    public function editAction($id)
    {
        // load existing field
        $em = $this->getDoctrine()->getEntityManager();
        $field = $em->getRepository($this->_getFieldFQCN())->find($id);
        if (!$field) {
            throw $this->createNotFoundException('No field found for id '.$id);
        }
        // prepare field form rendering
        $form = $this->createForm(new FieldType(), $field);
        return $this->render(
            'AkeneoCatalogBundle:Field:edit.html.twig', array('form' => $form->createView(),)
        );
    }

    /**
     * @Route("/field/save")
     * @Template()
     */
    public function saveAction(Request $request)
    {
        // load existing object or create a new one
        $postData = $request->get('akeneo_catalog_productfield');
        $id = isset($postData['id']) ? $postData['id'] : false;
        $em = $this->getDoctrine()->getEntityManager();
        if ($id) {
            $field = $em->getRepository($this->_getFieldFQCN())->find($id);
        } else {
            $field = new Attribute();
        }
        // create and bind with form
        $form = $this->createForm(new FieldType(), $field);
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                // save field
                $em->persist($field);
                $em->flush();
                // success message and redirect
                $this->get('session')->setFlash('notice', 'Field has been successfully saved');
                return $this->redirect($this->generateUrl('akeneo_catalog_field_edit', array('id' => $field->getId())));
            }
        }
    }

}
