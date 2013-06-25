<?php


namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;

/**
 * Class ConfigGridController
 * @package Oro\Bundle\EntityExtendBundle\Controller
 *
 * @Route("/entityextend")
 */
class ConfigFieldGridController extends Controller
{
    /**
     * @Route("/create", name="oro_entityextend_create")
     */
    public function createAction()
    {
        die('extend create');
    }

    /**
     * @Route("/update/{id}", name="oro_entityextend_update", requirements={"id"="\d+"}, defaults={"id"=0})
     */
    public function updateAction($id)
    {
        die('extend update');
    }

    /**
     * @Route("/remove/{id}", name="oro_entityextend_remove", requirements={"id"="\d+"}, defaults={"id"=0})
     */
    public function removeAction($id)
    {
        die('extend remove');
    }


}