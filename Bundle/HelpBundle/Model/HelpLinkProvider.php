<?php

namespace Oro\Bundle\HelpBundle\Model;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\HelpBundle\Annotation\Help;

class HelpLinkProvider
{
    /**
     * @var string
     */
    protected $requestController;

    /**
     * @var Help|null
     */
    protected $helpConfigurationAnnotation;

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
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $format = '%server%/%vendor%/%bundle%:%controller%_%action%';

    /**
     * @param ControllerNameParser $parser
     */
    public function __construct(ControllerNameParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $controller
     */
    public function setRequestController($controller)
    {
        $this->requestController = $controller;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getRequestController()
    {
        if (!$this->requestController) {
            throw new \RuntimeException('Request controller must be set.');
        }
        return $this->requestController;
    }

    /**
     * @param Help|null $configurationAnnotation
     */
    public function setHelpConfigurationAnnotation(Help $configurationAnnotation = null)
    {
        $this->helpConfigurationAnnotation = $configurationAnnotation;
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
            $link = strtr('%server%/%uri%', $replaceParams);
        } else {
            $link = strtr($this->format, $replaceParams);
        }

        $request = $this->request;
        $link = preg_replace_callback(
            '/{(\w+)}/',
            function ($matches) use ($request) {
                if (count($matches) > 1) {
                    return $request->get($matches[1]);
                } else {
                    return '';
                }
            },
            $link
        );

        return preg_replace('/(^:)\/+/', '/', $link);
    }

    /**
     * Get merged flat configuration for requested controller.
     *
     * @return array
     */
    protected function getConfiguration()
    {
        $controllerActionKey = $this->parser->build($this->getRequestController());
        $controllerNameParts = explode('::', $this->getRequestController());

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

        if ($this->helpConfigurationAnnotation) {
            $configuration = array_merge(
                $configuration,
                $this->helpConfigurationAnnotation->getConfigurationArray()
            );
        }

        return $configuration;
    }
}
