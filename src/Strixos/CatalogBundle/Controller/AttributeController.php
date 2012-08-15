<?php

namespace Strixos\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Strixos\CatalogBundle\Form\Type\AttributeType;
use Strixos\CatalogBundle\Entity\Attribute;

/**
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeController extends Controller
{
    /**
     * @Route("/attribute/index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $list = $em->getRepository('StrixosCatalogBundle:Attribute')
            ->findAll();
        return $this->render('StrixosCatalogBundle:Attribute:index.html.twig', array('attributes' => $list));
    }

    /**
    * @Route("/attribute/new")
    * @Template()
    */
    public function newAction(Request $request)
    {
        $attribute = new Attribute();
        $form = $this->createForm(new AttributeType(), $attribute);
        // render form
        return $this->render(
            'StrixosCatalogBundle:Attribute:edit.html.twig', array('form' => $form->createView(),)
        );
    }

    /**
     * @Route("/attribute/edit/{id}")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $attribute = $em->getRepository('StrixosCatalogBundle:Attribute')->find($id);
        if (!$attribute) {
            throw $this->createNotFoundException('No attribute found for id '.$id);
        }
        $form = $this->createForm(new AttributeType(), $attribute);
        // render form
        return $this->render(
            'StrixosCatalogBundle:Attribute:edit.html.twig', array('form' => $form->createView(),)
        );
    }

    /**
    * @Route("/attribute/save")
    * @Template()
    */
    public function saveAction(Request $request)
    {
        // load existing object or create a new one
        $postData = $request->get('strixos_catalog_attribute');
        $id = $postData['id'];
        $em = $this->getDoctrine()->getEntityManager();
        if ($id) {
            $attribute = $em->getRepository('StrixosCatalogBundle:Attribute')->find($id);
        } else {
            $attribute = new Attribute();
        }
        // create an array of the current persisted options objects
        $originalOptions = array();
        foreach ($attribute->getOptions() as $option) {
            $originalOptions[] = $option;
        }

        // create and bind with form
        $form = $this->createForm(new AttributeType(), $attribute);
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                // filter original with no longer present
                foreach ($attribute->getOptions() as $option) {
                    foreach ($originalOptions as $key => $toDelete) {
                        if ($toDelete->getId() === $option->getId()) {
                            unset($originalOptions[$key]);
                        }
                    }
                }
                // remove option to delete
                foreach ($originalOptions as $option) {
                    $em->remove($option);
                }
                // persist existing and new options
                foreach ($attribute->getOptions() as $option) {
                    $option->setAttribute($attribute);
                    $em->persist($option);
                }
                // persist attribute
                $em->persist($attribute);
                $em->flush();
                // success message and redirect
                $this->get('session')->setFlash('notice', 'Attribute has been saved!');
                return $this->redirect(
                    $this->generateUrl('strixos_catalog_attribute_edit', array('id' => $attribute->getId()))
                );
            }
        }
        // TODO Exception
    }

}
