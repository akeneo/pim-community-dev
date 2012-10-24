<?php
namespace Akeneo\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Akeneo\CatalogBundle\Entity\ProductField;
use Akeneo\CatalogBundle\Document\ProductFieldMongo;
use Akeneo\CatalogBundle\Form\ProductFieldType;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\TextColumn;

/**
 * Product field controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/productfield")
 */
class ProductFieldController extends AbstractProductController
{

    /**
     * (non-PHPdoc)
     * @see Parent
     */
    public function getObjectShortName()
    {
        return $this->container->getParameter('pim.catalog.product.field.class');
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
        $rowAction = new RowAction('Edit', 'akeneo_catalog_productfield_edit');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('AkeneoCatalogBundle:ProductField:index.html.twig');
    }

    /**
     * Displays a form to create a new field
     *
     * @Route("/new")
     * @Template()
     */
    public function newAction()
    {
        $entity = $this->getNewObject();
        $form   = $this->createForm(new ProductFieldType(), $entity);

        // render form
        return $this->render(
            'AkeneoCatalogBundle:ProductField:new.html.twig', array('entity' => $entity, 'form' => $form->createView())
        );
    }

    /**
     * Creates a new field
     *
     * @Route("/create")
     * @Method("POST")
     * @Template("AkeneoCatalogBundle:ProductField:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = $this->getNewObject();
        $form = $this->createForm(new ProductFieldType(), $entity);
        $form->bind($request);

        // TODO : avoid to create product field with same code -> complete validation !
        if ($form->isValid()) {
            $manager = $this->get($this->getObjectManagerService());
            $manager->persist($entity);
            $manager->flush();
            $this->get('session')->setFlash('notice', 'Field has been created');

            return $this->redirect($this->generateUrl('akeneo_catalog_productfield_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing field entity.
     *
     * @Route("/{id}/edit")
     * @Template()
     */
    public function editAction($id)
    {
        $manager = $this->get($this->getObjectManagerService());

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product field.');
        }

        $editForm = $this->createForm(new ProductFieldType(), $entity);

        $params = array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        );

        // render form
        return $this->render('AkeneoCatalogBundle:ProductField:edit.html.twig', $params);
    }

    /**
     * Edits an existing field entity.
     *
     * @Route("/{id}/update")
     * @Method("POST")
     * @Template("AkeneoCatalogBundle:ProductField:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->get($this->getObjectManagerService());

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product field.');
        }

        $editForm = $this->createForm(new ProductFieldType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $manager->persist($entity);
            $manager->flush();
            $this->get('session')->setFlash('notice', 'Field has been updated');

            return $this->redirect($this->generateUrl('akeneo_catalog_productfield_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Deletes a ProductField entity.
     *
     * @Route("/{id}/delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $manager = $this->get($this->getObjectManagerService());
        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product field.');
        }

        $manager->remove($entity);
        $manager->flush();

        $this->get('session')->setFlash('notice', 'Field has been deleted');

        return $this->redirect($this->generateUrl('akeneo_catalog_productfield_index'));
    }

}
