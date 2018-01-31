<?php

namespace Pim\Bundle\RegistryOfCurrentNumberBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('RegistryOfCurrentNumberBundle:Default:index.html.twig');
    }
}
