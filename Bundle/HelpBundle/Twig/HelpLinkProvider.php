<?php

namespace Oro\Bundle\HelpBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\HelpBundle\Annotation\Help;

class HelpLinkProvider
{
    /**
     * @var string
     */
    protected $controller;

    /**
     * @var Help|null
     */
    protected $configurationAnnotation;

    /**
     * @var array
     */
    protected $rawConfiguration;

    /**
     * @var ControllerNameParser
     */
    protected $parser;

    /**
     * @param ControllerNameParser $parser
     * @param Request $request
     */
    public function __construct(ControllerNameParser $parser, Request $request)
    {
        $this->parser = $parser;
        $this->controller = $request->get('_controller');
        $this->configurationAnnotation = $request->get(Help::ALIAS);
    }

    /**
     * Set configuration.
     *
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        $this->rawConfiguration = $configuration;
    }

    /**
     * Get help link URL.
     *
     * @return string
     */
    public function getHelpLinkUrl()
    {
        $configuration = $this->getConfiguration();

        return '';
    }

    protected function getConfiguration()
    {
        $controllerActionKey = $this->parser->build($this->controller);
        $controllerNameParts = explode('::', $this->controller);

        $controllerNamespaceInfo = explode('\\', $controllerNameParts[0]);
        $vendorKey = $controllerNamespaceInfo[0];

        $controllerNamespaceInfo = explode(':', $controllerActionKey);
        $bundleKey = $controllerNamespaceInfo[0];
        $controllerKey = $bundleKey . ':' . $controllerNamespaceInfo[1];

        $configuration = array();
        $searchKeys = array($vendorKey, $bundleKey, $controllerKey, $controllerActionKey);
        foreach ($searchKeys as $searchKey) {
            if (array_key_exists($searchKey, $this->rawConfiguration)) {
                $configuration += $this->rawConfiguration[$searchKey];
            }
        }

        if ($this->configurationAnnotation) {

        }

        return $configuration;
    }
}
