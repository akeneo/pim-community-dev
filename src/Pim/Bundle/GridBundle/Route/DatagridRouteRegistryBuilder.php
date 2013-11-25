<?php

namespace Pim\Bundle\GridBundle\Route;

use Symfony\Component\Routing\RouterInterface;
use Pim\Bundle\GridBundle\Exception\JavascriptRegexpTranslatorException;

/**
 * Builds a registry of datagrid routes
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridRouteRegistryBuilder
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var array An array of datagrid routes indexed by datagrid name
     */
    protected $routes = array();

    /**
     * @var array An array of parameter replacesments, indexed by datagrid name
     */
    protected $paramReplacements;

    /**
     * Constructor
     *
     * @param Router $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Adds a route to the registry
     *
     * @param string $datagridName
     * @param string $routeName
     */
    public function addRoute($datagridName, $routeName)
    {
        $this->routes[$datagridName] = $routeName;
    }

    /**
     * Adds a parameter replacement to a route
     * @param string $datagridName
     * @param string $parameterName
     * @param string $parameterValue
     */
    public function addParameterReplacement($datagridName, $parameterName, $parameterValue)
    {
        if (!isset($this->paramReplacements[$datagridName])) {
            $this->paramReplacements[$datagridName] = array();
        }
        $this->paramReplacements[$datagridName][$parameterName] = $parameterValue;
    }

    /**
     * Returns an array of regexps for each configured route, indexed by datagrid name
     *
     * @return array
     */
    public function getRegexps()
    {
        $regexps = array();
        $routeCollection = $this->router->getRouteCollection();
        $translator = new JavascriptRegExpTranslator();

        foreach ($this->routes as $datagridName => $routeName) {
            $route = $routeCollection->get($routeName);
            if ($route) {
                try {
                    $regexps[$datagridName] = $translator->translate(
                        $route->compile()->getRegex(),
                        isset($this->paramReplacements[$datagridName])
                        ? $this->paramReplacements[$datagridName]
                        : array()
                    );
                } catch (JavascriptRegexpTranslatorException $ex) {
                }
            }
        }

        return $regexps;
    }
}
