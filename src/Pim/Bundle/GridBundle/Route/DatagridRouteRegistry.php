<?php

namespace Pim\Bundle\GridBundle\Route;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Pim\Bundle\GridBundle\Exception\JavascriptRegexpTranslatorException;
/**
 * Registry of datagrid routes
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridRouteRegistry
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var array An array of datagrid routes indexed by datagrid name
     */
    protected $routes;

    public function __construct(Router $router)
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
     * Returns an array of regexps for each configured route, indexed by datagrid name
     * 
     * @return array
     */
    public function getRegexps()
    {
        $regexps = array();
        $routes = $this->router->getRouteCollection();
        $translator = new JavascriptRegExpTranslator($this->router->getContext()->getBaseUrl());
        foreach ($this->routes as $datagridName => $routeName) {
            $route = $routes->get($routeName);
            if ($route) {
                try {
                    $regexps[$datagridName] = $translator->translate($route->compile()->getRegex());
                } catch (JavascriptRegexpTranslatorException $ex) {
                }
            }
        }
        
        return $regexps;
    }
}
