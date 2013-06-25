<?php

namespace Oro\Bundle\EntityFormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('OroEntityFormBundle:Default:index.html.twig', array('name' => $name));
    }
}
