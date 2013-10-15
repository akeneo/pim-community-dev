<?php

namespace Oro\Bundle\HelpBundle\Model;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\HelpBundle\Annotation\Help;

class HelpLinkProvider
{
    /**
     * @var array
     */
    protected $rawConfiguration;

    /**
     * @var ControllerNameParser
     */
    protected $parser;

    /**
     * @var array
     */
    protected $parserCache = array();

    /**
     * @var string
     */
    protected $groupSeparator = '/';

    /**
     * @var string
     */
    protected $requestController;

    /**
     * @var string
     */
    protected $requestRoute;

    /**
     * @var Help|null
     */
    protected $helpConfigurationAnnotation;

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

    public function setRequest(Request $request)
    {
        $this->requestController = $request->get('_controller');
        $this->requestRoute = $request->get('_route');
        $this->helpConfigurationAnnotation = $request->get('_' . Help::ALIAS);
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
        $config = $this->getConfiguration();
        if (isset($config['link'])) {
            return $config['link'];
        }

        $config['server'] = rtrim($config['server'], '/');
        if (isset($config['prefix'], $config['vendor'])) {
            $config['vendor'] = $config['prefix'] . $this->groupSeparator . $config['vendor'];
        }

        $keys = array('server', 'vendor', 'bundle', 'controller', 'action', 'uri');
        $replaceParams = array();
        foreach ($keys as $key) {
            $replaceParams['%' . $key . '%'] = isset($config[$key]) ? $config[$key]: '';
        }

        if (isset($config['uri'])) {
            return strtr('%server%/%uri%', $replaceParams);
        } elseif (isset($config['vendor'], $config['bundle'], $config['controller'], $config['action'])) {
            return strtr($this->format, $replaceParams);
        } else {
            return $config['server'];
        }
    }

    /**
     * Get merged flat configuration for requested controller.
     *
     * @return array
     */
    protected function getConfiguration()
    {
        $result = array();

        $this->mergeDefaultsConfig($result);
        $this->mergeRequestControllerConfig($result);
        $this->mergeAnnotationConfig($result);
        $this->mergeRoutesConfig($result);
        $this->mergeVendorsAndResourcesConfig($result);

        return $result;
    }

    /**
     * Apply configuration from "defaults" section of configuration
     *
     * @param array $resultConfig
     */
    protected function mergeDefaultsConfig(array &$resultConfig)
    {
        $resultConfig = array_merge($resultConfig, $this->rawConfiguration['defaults']);
    }

    /**
     * Apply configuration from annotations
     *
     * @param array $resultConfig
     */
    protected function mergeAnnotationConfig(array &$resultConfig)
    {
        if ($this->helpConfigurationAnnotation) {
            $resultConfig = array_merge($resultConfig, $this->helpConfigurationAnnotation->getConfigurationArray());
        }
    }

    /**
     * Apply configuration from "routes" section of configuration
     *
     * @param array $resultConfig
     */
    protected function mergeRoutesConfig(array &$resultConfig)
    {
        if ($this->requestRoute && isset($this->rawConfiguration['routes'][$this->requestRoute])) {
            $resultConfig = array_merge($resultConfig, $this->rawConfiguration['routes'][$this->requestRoute]);
        }
    }

    /**
     * Apply configuration from request controller name
     *
     * @param array $resultConfig
     */
    protected function mergeRequestControllerConfig(array &$resultConfig)
    {
        if (!$this->requestController) {
            return;
        }

        $resultConfig = array_merge($resultConfig, $this->parseRequestController($this->requestController));
    }

    /**
     * Apply configuration from "vendors" and "resources" section of configuration
     *
     * @param array $resultConfig
     */
    protected function mergeVendorsAndResourcesConfig(array &$resultConfig)
    {
        if (!$this->requestController) {
            return;
        }

        $controllerData = $this->parseRequestController($this->requestController);

        if (!$controllerData) {
            return;
        }

        $vendor = $controllerData['vendor'];
        $bundle = $controllerData['bundle'];
        $controller = $controllerData['controller'];
        $action = $controllerData['action'];

        $configData[] = array(
            'id' => $vendor,
            'section' => 'vendors',
            'key' => 'vendor',
            'value' => $vendor
        );
        $configData[] = array(
            'id' => $bundle,
            'section' => 'resources',
            'key' => 'bundle',
            'value' => $bundle
        );
        $configData[] = array(
            'id' => $bundle . ':' . $controller,
            'section' => 'resources',
            'key' => 'controller',
            'value' => $controller
        );
        $configData[] = array(
            'id' => sprintf('%s:%s:%s', $bundle, $controller, $action),
            'section' => 'resources',
            'key' => 'action',
            'value' => $action
        );

        foreach ($configData as $searchData) {
            $id = $searchData['id'];
            $section = $searchData['section'];

            $key = $searchData['key'];
            $value = isset($searchData['value']) ? $searchData['value'] : null;

            if (isset($this->rawConfiguration[$section][$id])) {
                $rawConfiguration = $this->rawConfiguration[$section][$id];
                if (isset($rawConfiguration['alias'])) {
                    $value = $rawConfiguration['alias'];
                    unset($rawConfiguration['alias']);
                }
                $resultConfig = array_merge($resultConfig, $rawConfiguration);
                $resultConfig[$key] = $value;

            }
        }
    }

    /**
     * Parses request controller and returns vendor, bundle, controller, action
     *
     * @param string $controller
     * @return array
     */
    protected function parseRequestController($controller)
    {
        if (!is_string($controller)) {
            return array();
        }

        if (array_key_exists($controller, $this->parserCache)) {
            return $this->parserCache[$controller];
        }

        $result = array();

        if ($this->isControllerActionFullName($controller)) {
            // Format: "Foo\BarBundle\Controller\BazController::indexAction"
            $controllerActionKey = $this->parser->build($controller);
            $controllerFullName = $controller;
        } elseif ($this->isControllerActionShortName($controller)) {
            // Format: "FooBarBundle:BazController:index"
            $controllerActionKey = $controller;
            $controllerFullName = $this->parser->parse($controller);
        } else {
            // Format with service id: "foo_bar_bundle.baz_controller:indexAction"
            // Cannot be used to parse vendor, bundle, controller, action
            return $result;
        }

        $controllerNameParts = explode('::', $controllerFullName);
        $vendorName = current(explode('\\', $controllerNameParts[0]));

        list($bundleName, $controllerName, $actionName) = explode(':', $controllerActionKey);

        return $this->parserCache[$controller] = array(
            'vendor' => $vendorName,
            'bundle' => $bundleName,
            'controller' => $controllerName,
            'action' => $actionName,
        );
    }

    /**
     * Check if controller has format "Bundle:Controller:action"
     *
     * @param string $controller
     * @return bool
     */
    protected function isControllerActionShortName($controller)
    {
        return 3 === count(explode(':', $controller));
    }

    /**
     * Check if controller has format Foo\BarBundle\Controller\BazController::indexAction
     *
     * @param string $controller
     * @return int
     */
    protected function isControllerActionFullName($controller)
    {
        return preg_match('#^(.*?\\\\Controller\\\\(.+)Controller)::(.+)Action$#', $controller, $match);
    }
}
