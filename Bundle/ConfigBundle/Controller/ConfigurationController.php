<?php

namespace Oro\Bundle\ConfigBundle\Controller;

use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\NavigationBundle\Annotation\TitleTemplate;

class ConfigurationController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     * @TitleTemplate("Configuration index page")
     */
    public function indexAction()
    {
        $data = Yaml::parse(realpath(__DIR__ . '/../Resources/config/system_configuration.yml'));

        $processor = new ProcessorDecorator();

        return array('data' => $processor->process($data));
    }
}
