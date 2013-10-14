<?php

namespace Oro\Bundle\HelpBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $groupSeparator = '/';

    /**
     * @var string
     */
    protected $format = '%server%/%vendor%/%bundle%:%controller%_%action%';

    /**
     * @param ControllerNameParser $parser
     * @param ContainerInterface $container
     */
    public function __construct(ControllerNameParser $parser, ContainerInterface $container)
    {
        $this->parser = $parser;
        $this->container = $container;
    }

    /**
     * @return string
     */
    protected function getController()
    {
        if (!$this->controller) {
            $this->controller = $this->getRequest()->get('_controller');
        }
        return $this->controller;
    }

    /**
     * @return Help
     */
    protected function getConfigurationAnnotation()
    {
        if (!$this->configurationAnnotation) {
            $this->configurationAnnotation = $this->getRequest()->get(Help::ALIAS);
        }
        return $this->configurationAnnotation;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->container->get('request');
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
        if (isset($configuration['link'])) {
            return $configuration['link'];
        }

        $configuration['server'] = rtrim($configuration['server'], '/');
        if (isset($configuration['prefix'])) {
            $configuration['vendor'] = $configuration['prefix'] . $this->groupSeparator . $configuration['vendor'];
        }

        $keys = array('server', 'vendor', 'bundle', 'controller', 'action', 'uri');
        $replaceParams = array();
        foreach ($keys as $key) {
            $replaceParams['%' . $key . '%'] = isset($configuration[$key]) ? $configuration[$key]: '';
        }

        if (isset($configuration['uri'])) {
            return strtr('%server%/%uri%', $replaceParams);
        } else {
            return strtr($this->format, $replaceParams);
        }
    }

    /**
     * Get merged flat configuration for requested controller.
     *
     * @return array
     */
    protected function getConfiguration()
    {
        $controllerActionKey = $this->parser->build($this->getController());
        $controllerNameParts = explode('::', $this->getController());

        $controllerNamespaceInfo = explode('\\', $controllerNameParts[0]);
        $vendorName = $controllerNamespaceInfo[0];

        list($bundleName, $controllerName, $actionName) = explode(':', $controllerActionKey);
        $configData = array(
            'vendor' => array(
                'key' => $vendorName,
                'alias' => $vendorName,
                'section' => 'vendors'
            ),
            'bundle' => array(
                'key' => $bundleName,
                'alias' => $bundleName,
                'section' => 'resources'
            ),
            'controller' => array(
                'key' => $bundleName . ':' . $controllerName,
                'alias' => $controllerName,
                'section' => 'resources'
            ),
            'action' => array(
                'key' => $controllerActionKey,
                'alias' => $actionName,
                'section' => 'resources'
            )
        );

        $configuration = $this->rawConfiguration['defaults'];
        foreach ($configData as $keyName => $searchData) {
            $keyIdentifier = $searchData['alias'];
            $section = $searchData['section'];
            $key = $searchData['key'];
            if (array_key_exists($section, $this->rawConfiguration)
                && array_key_exists($key, $this->rawConfiguration[$section])
            ) {
                $rawConfiguration = $this->rawConfiguration[$section][$key];
                if (isset($rawConfiguration['alias'])) {
                    $keyIdentifier = $rawConfiguration['alias'];
                    unset($rawConfiguration['alias']);
                }
                $configuration = array_merge($configuration, $rawConfiguration);
            }
            $configuration[$keyName] = $keyIdentifier;
        }

        if ($this->getConfigurationAnnotation()) {
            $configuration = array_merge(
                $configuration,
                $this->getConfigurationAnnotation()->getConfigurationArray()
            );
        }

        return $configuration;
    }
}
