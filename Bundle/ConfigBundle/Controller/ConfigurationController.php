<?php

namespace Oro\Bundle\ConfigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ConfigurationController extends Controller
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
