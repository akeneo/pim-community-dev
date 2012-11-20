<?php
namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Form\Type\ChannelType;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Channel controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/channel")
 */
class ChannelController extends Controller
{

    /**
     * (non-PHPdoc)
     * @see Parent
     * @return string
     */
    public function getObjectShortName()
    {
        return 'PimCatalogBundle:Channel';
    }

    /**
     * Get used object manager
     * @return string
     */
    public function getObjectManagerService()
    {
        return 'doctrine.orm.entity_manager';
    }

    /**
     * Return full name of object class
     * @return unknown
     */
    public function getObjectClassFullName()
    {
        $om = $this->get($this->getObjectManagerService());
        $metadata = $om->getClassMetadata($this->getObjectShortName());
        $classFullName = $metadata->getName();

        return $classFullName;
    }

    /**
     * Return new instance of object
     * @return unknown
     */
    public function getNewObject()
    {
         $classFullName = $this->getObjectClassFullName();
        $entity = new $classFullName();

        return $entity;
    }

    /**
     * Lists all channels
     *
     * @Route("/index")
     * @Template()
     * 
     * @return multitype
     */
    public function indexAction()
    {
        $source = new GridEntity($this->getObjectShortName());
        $grid = $this->get('grid');
        $grid->setSource($source);

        // add an action column
        $rowAction = new RowAction('Edit', 'pim_catalog_channel_edit');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        // add an action column
        $rowAction = new RowAction('Delete', 'pim_catalog_channel_delete');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        return $grid->getGridResponse('PimCatalogBundle:Channel:index.html.twig');
    }

    /**
     * Displays a form to create a new channel
     *
     * @Route("/new")
     * @Template()
     * 
     * @return multitype
     */
    public function newAction()
    {
        $entity = $this->getNewObject();
        $classFullName = $this->getObjectClassFullName();
        $localeClassFullName = 'Pim\Bundle\CatalogBundle\Entity\ChannelLocale';
        $form = $this->createForm(new ChannelType($classFullName, $localeClassFullName), $entity);
        $formAction = $this->generateUrl('pim_catalog_channel_create');
        // render form
        return $this->render(
            'PimCatalogBundle:Channel:edit.html.twig', array('entity' => $entity, 'form' => $form->createView(), 'formAction' => $formAction)
        );
    }

    /**
     * Disable old default channel
     */
    protected function disableOldDefaultChannel()
    {
        $manager = $this->get($this->getObjectManagerService());
        $channels = $manager->getRepository('PimCatalogBundle:Channel')
            ->findBy(array('isDefault' => 1));
        foreach ($channels as $channel) {
            $channel->setIsDefault(false);
            $manager->persist($channel);
        }
    }

    /**
    * Disable old default channel
    * 
    * @return boolean
    */
    protected function hasDefaultChannel()
    {
        $manager = $this->get($this->getObjectManagerService());
        $channels = $manager->getRepository('PimCatalogBundle:Channel')
            ->findBy(array('isDefault' => 1));

        return (count($channels) > 0);
    }

    /**
     * Check if there is one default locale
     * @param Channel $entity
     * 
     * @return boolean
     */
    protected function hasDefaultLocale($entity)
    {
        $hasDefault = 0;
        // check there is only one default locale
        foreach ($entity->getLocales() as $locale) {
            if ($locale->getIsDefault()) {
                $hasDefault++;
            }
        }
        if ($hasDefault != 1) {
            $this->get('session')->setFlash('error', 'A channel needs only one default locale');

            return false;
        }

        return true;
    }

    /**
     * Creates a new channel
     *
     * @param Request $request
     * 
     * @Route("/create")
     * @Method("POST")
     * 
     * @return multitype
     */
    public function createAction(Request $request)
    {
        $entity  = $this->getNewObject();
        $classFullName = $this->getObjectClassFullName();
        $localeClassFullName = 'Pim\Bundle\CatalogBundle\Entity\ChannelLocale';
        $form = $this->createForm(new ChannelType($classFullName, $localeClassFullName), $entity);
        $form->bind($request);

        if ($form->isValid()) {

            $manager = $this->get($this->getObjectManagerService());
            $manager->persist($entity);

            // change old default channel
            if ($entity->getIsDefault()) {
                $this->disableOldDefaultChannel();
            }
            // has default locale
            if (!$this->hasDefaultLocale($entity)) {
                return $this->redirect($this->generateUrl('pim_catalog_channel_new'));
            }

            $manager->flush();

            $this->get('session')->setFlash('success', 'Channel has been created');

            return $this->redirect($this->generateUrl('pim_catalog_channel_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing channel entity.
     *
     * @param integer $id
     *
     * @Route("/{id}/edit")
     * @Template()
     * 
     * @return multitype
     */
    public function editAction($id)
    {
        $manager = $this->get($this->getObjectManagerService());

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find channel.');
        }

        $classFullName = $this->getObjectClassFullName();
        $localeClassFullName = 'Pim\Bundle\CatalogBundle\Entity\ChannelLocale';
        $editForm = $this->createForm(new ChannelType($classFullName, $localeClassFullName), $entity);
        $formAction = $this->generateUrl('pim_catalog_channel_update', array('id' => $entity->getId()));

        $params = array(
            'entity'     => $entity,
            'form'       => $editForm->createView(),
            'formAction' => $formAction
        );

        // render form
        return $this->render('PimCatalogBundle:Channel:edit.html.twig', $params);
    }

    /**
     * Edits an existing channel entity.
     * 
     * @param Request $request request
     * @param integer $id      channel id
     * 
     * @Route("/{id}/update")
     * @Method("POST")
     * 
     * @return multitype
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->get($this->getObjectManagerService());

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find channel.');
        }

        $classFullName = $this->getObjectClassFullName();
        $localeClassFullName = 'Pim\Bundle\CatalogBundle\Entity\ChannelLocale';
        $editForm = $this->createForm(new ChannelType($classFullName, $localeClassFullName), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $manager->persist($entity);

            // change old default channel
            if ($entity->getIsDefault()) {
                $this->disableOldDefaultChannel();
            // check there is a default channel
            } elseif (!$this->hasDefaultChannel()) {
                $this->get('session')->setFlash('error', 'There is no default channel');

                return $this->redirect($this->generateUrl('pim_catalog_channel_edit', array('id' => $id)));
            } elseif (!$this->hasDefaultLocale($entity)) {
                return $this->redirect($this->generateUrl('pim_catalog_channel_edit', array('id' => $id)));
            }

            $manager->flush();
            $this->get('session')->setFlash('success', 'Channel has been updated');

            return $this->redirect($this->generateUrl('pim_catalog_channel_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Delete an existing channel entity.
     *
     * @param integer $id
     *
     * @Route("/{id}/delete")
     * @Template()
     * 
     * @return multitype
     */
    public function deleteAction($id)
    {
        $manager = $this->get($this->getObjectManagerService());
        $entity = $manager->getRepository($this->getObjectShortName())->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find channel.');
        }
        // delete
        $manager->remove($entity);
        $manager->flush();
        $this->get('session')->setFlash('success', "Channel '{$entity->getCode()}' has been delete");

        return $this->redirect($this->generateUrl('pim_catalog_channel_index'));
    }

}
