<?php

namespace Oro\Bundle\ConfigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\NavigationBundle\Annotation\TitleTemplate;
use Oro\Bundle\ConfigBundle\DependencyInjection\Compiler\SystemConfigurationPass;

class ConfigurationController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     * @TitleTemplate("Configuration merge example")
     */
    public function indexAction()
    {
        return array('data' => $this->container->getParameter(SystemConfigurationPass::CONFIG_PARAM_NAME));
    }
}
