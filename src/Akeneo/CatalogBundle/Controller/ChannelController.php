<?php
namespace Akeneo\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Akeneo\CatalogBundle\Form\ChannelType;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Channel controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/channel")
 */
class ChannelController extends AbstractProductController
{

    /**
     * (non-PHPdoc)
     * @see Parent
     */
    public function getObjectShortName()
    {
        return 'AkeneoCatalogBundle:Channel';
    }

    /**
     * Get used object manager
     */
    public function getObjectManagerService()
    {
        return 'doctrine.orm.entity_manager';
    }

    /**
     * Lists all channels
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $source = $this->getGridSource();
        $grid = $this->get('grid');
        $grid->setSource($source);

        // add an action column
        $rowAction = new RowAction('Edit', 'akeneo_catalog_channel_edit');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);


        return $grid->getGridResponse('AkeneoCatalogBundle:Channel:index.html.twig');
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
        $classFullName = $this->getObjectClassFullName();
        $localeClassFullName = 'Akeneo\CatalogBundle\Entity\ChannelLocale';
        $form = $this->createForm(new ChannelType($classFullName, $localeClassFullName), $entity);

        // render form
        return $this->render(
            'AkeneoCatalogBundle:Channel:new.html.twig', array('entity' => $entity, 'form' => $form->createView())
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
        $classFullName = $this->getObjectClassFullName();
        $localeClassFullName = 'Akeneo\CatalogBundle\Entity\ChannelLocale';
        $form = $this->createForm(new ChannelType($classFullName, $localeClassFullName), $entity);
        $form->bind($request);

        if ($form->isValid()) {

            $manager = $this->get($this->getObjectManagerService());
            $manager->persist($entity);
            $manager->flush();

            $this->get('session')->setFlash('notice', 'Channel has been created');

            return $this->redirect($this->generateUrl('akeneo_catalog_channel_edit', array('id' => $entity->getId())));
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

        $classFullName = $this->getObjectClassFullName();
        $localeClassFullName = 'Akeneo\CatalogBundle\Entity\ChannelLocale';
        $editForm = $this->createForm(new ChannelType($classFullName, $localeClassFullName), $entity);

        $params = array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        );

        // render form
        return $this->render('AkeneoCatalogBundle:Channel:edit.html.twig', $params);
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
            throw $this->createNotFoundException('Unable to find channel.');
        }

        $classFullName = $this->getObjectClassFullName();
        $localeClassFullName = 'Akeneo\CatalogBundle\Entity\ChannelLocale';
        $editForm = $this->createForm(new ChannelType($classFullName, $localeClassFullName), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $manager->persist($entity);
            $manager->flush();
            $this->get('session')->setFlash('notice', 'Channel has been updated');

            return $this->redirect($this->generateUrl('akeneo_catalog_channel_edit', array('id' => $id)));
        }
        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

}
