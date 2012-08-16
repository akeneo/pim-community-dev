<?php

namespace Strixos\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Strixos\CatalogBundle\Form\Type\AttributeSetType;
use Strixos\CatalogBundle\Entity\AttributeSet;

/**
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeSetController extends Controller
{
    /**
     * @Route("/attributeset/index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $sets = $em->getRepository('StrixosCatalogBundle:AttributeSet')
            ->findAll();
        return $this->render('StrixosCatalogBundle:AttributeSet:index.html.twig', array('sets' => $sets));
    }

    /**
    * @Route("/attributeset/new")
    * @Template()
    */
    public function newAction(Request $request)
    {
        $set = new AttributeSet();
        $form = $this->createForm(new AttributeSetType(), $set);
        // render form
        return $this->render(
            'StrixosCatalogBundle:AttributeSet:edit.html.twig', array('form' => $form->createView(),)
        );
    }

    /**
     * @Route("/attributeset/edit/{id}")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $set = $em->getRepository('StrixosCatalogBundle:AttributeSet')->find($id);
        if (!$set) {
            throw $this->createNotFoundException('No set found for id '.$id);
        }
        $form = $this->createForm(new AttributeSetType(), $set);
        // render form
        return $this->render(
            'StrixosCatalogBundle:AttributeSet:edit.html.twig', array('form' => $form->createView(),)
        );
    }
}
