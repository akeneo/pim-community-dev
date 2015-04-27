<?php

namespace PamEnterprise\Bundle\AssetManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('PamEnterpriseAssetManagementBundle:Default:index.html.twig', array('name' => $name));
    }
}
