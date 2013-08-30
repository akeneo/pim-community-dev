<?php

namespace Oro\Bundle\ConfigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\NavigationBundle\Annotation\TitleTemplate;
use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;

class ConfigurationController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     * @TitleTemplate("Configuration merge example")
     */
    public function indexAction()
    {
        $data = array();
        $processor = new ProcessorDecorator();
        foreach (array('system_configuration.yml', 'test.yml', 'test2.yml') as $fn) {
            $data = $processor->merge($data, Yaml::parse(realpath(__DIR__ . '/../Resources/config/' . $fn)));
        }

        return array('data' => $processor->process($data));
    }
}
