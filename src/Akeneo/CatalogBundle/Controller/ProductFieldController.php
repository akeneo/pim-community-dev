<?php

namespace Akeneo\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Akeneo\CatalogBundle\Entity\ProductField;
use Akeneo\CatalogBundle\Form\ProductFieldType;

/**
 * ProductField controller.
 *
 * @Route("/productfield")
 */
class ProductFieldController extends Controller
{
    /**
     * Lists all ProductField entities.
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AkeneoCatalogBundle:ProductField')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a ProductField entity.
     *
     * @Route("/{id}/show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AkeneoCatalogBundle:ProductField')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProductField entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new ProductField entity.
     *
     * @Route("/new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ProductField();
        $form   = $this->createForm(new ProductFieldType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new ProductField entity.
     *
     * @Route("/create")
     * @Method("POST")
     * @Template("AkeneoCatalogBundle:ProductField:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new ProductField();
        $form = $this->createForm(new ProductFieldType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('productfield_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ProductField entity.
     *
     * @Route("/{id}/edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AkeneoCatalogBundle:ProductField')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProductField entity.');
        }

        $editForm = $this->createForm(new ProductFieldType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing ProductField entity.
     *
     * @Route("/{id}/update")
     * @Method("POST")
     * @Template("AkeneoCatalogBundle:ProductField:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AkeneoCatalogBundle:ProductField')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProductField entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ProductFieldType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('productfield_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a ProductField entity.
     *
     * @Route("/{id}/delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AkeneoCatalogBundle:ProductField')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProductField entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('productfield_index'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
