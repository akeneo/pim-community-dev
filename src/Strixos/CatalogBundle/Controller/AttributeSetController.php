<?php

namespace Strixos\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
}
