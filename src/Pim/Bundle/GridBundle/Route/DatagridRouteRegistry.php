<?php

namespace Pim\Bundle\GridBundle\Route;

use Symfony\Component\Routing\RouterInterface;

/**
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridRouteRegistry
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var DatagridRouteRegistryBuilder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $regexps;

    /**
     * Constructor 
     * 
     * @param RouterInterface $router
     * @param DatagridRouteRegistryBuilder $builder
     * @param string $cacheDir
     */
    public function __construct(RouterInterface $router, DatagridRouteRegistryBuilder $builder, $cacheDir = null)
    {
        $this->router = $router;
        $this->builder = $builder;

        if (null !== $cacheDir && is_file($cache = $cacheDir.'/pim_datagrid_js_routes.php')) {
            $this->regexps = require $cache;
        }
    }

    /**
     * Returns an array of regexps for each configured route, indexed by datagrid name
     * 
     * @return array
     */
    public function getRegexps()
    {
        if (!isset($this->regexps)) {
            $this->regexps = $this->builder->getRegexps();
        }
        $prefix = str_replace('/', '\\/', $this->router->getContext()->getBaseUrl());

        return array_map(
            function ($regexp) use ($prefix) {
                return str_replace('%prefix%', $prefix, $regexp);
            },
            $this->regexps
        );
    }
}
