<?php

namespace Oro\Bundle\ConfigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\NavigationBundle\Annotation\TitleTemplate;

class ConfigurationController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     * @TitleTemplate("System configuration")
     */
    public function indexAction($configurationTreeName = 'system_configuration')
    {
        $provider = $this->container->get('oro_config.provider.form_provider');

        return array('data' => $provider->getFormData($configurationTreeName));
    }
}
