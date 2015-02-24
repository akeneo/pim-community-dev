<?php

namespace Acme\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $cars = $this->getDoctrine()->getRepository('AcmeAppBundle:Car')->findAll();

        return $this->render('AcmeAppBundle:Default:cars.html.twig', array('cars' => $cars));
    }
}
