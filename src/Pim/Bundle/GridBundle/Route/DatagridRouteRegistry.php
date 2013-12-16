<?php

namespace Pim\Bundle\GridBundle\Route;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Config\ConfigCache;

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
     * The name of the cache file
     * @staticvar string
     */
    const CACHE_FILE = 'pim_datagrid_js_routes';

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
     * @var boolean
     */
    protected $debugMode;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * Constructor
     *
     * @param RouterInterface              $router
     * @param DatagridRouteRegistryBuilder $builder
     * @param string                       $cacheDir
     * @param boolean                      $debugMode
     */
    public function __construct(
        RouterInterface $router,
        DatagridRouteRegistryBuilder $builder,
        $cacheDir = null,
        $debugMode = false
    ) {
        $this->router = $router;
        $this->builder = $builder;
        $this->cacheDir = $cacheDir;
        $this->debugMode = $debugMode;
    }

    /**
     * Returns an array of regexps for each configured route, indexed by datagrid name
     *
     * @return array
     */
    public function getRegexps()
    {
        if (!isset($this->regexps)) {
            $this->setRegexpsFromCache();
        }
        $prefix = str_replace('/', '\\/', $this->router->getContext()->getBaseUrl());

        return array_map(
            function ($regexp) use ($prefix) {
                return str_replace('%prefix%', $prefix, $regexp);
            },
            $this->regexps
        );
    }

    /**
     * Sets the regexps from the cache
     *
     * @return null
     */
    protected function setRegexpsFromCache()
    {
        if (null == $this->cacheDir) {
            $this->regexps = $this->builder->getRegexps();

            return;
        }

        $cache = new ConfigCache(sprintf('%s/%s', $this->cacheDir, self::CACHE_FILE), $this->debugMode);
        if (!$cache->isFresh()) {
            $cache->write(
                sprintf('<?php return %s;', var_export($this->builder->getRegexps(), true)),
                $this->router->getRouteCollection()->getResources()
            );
        }

        $this->regexps = include $cache;
    }
}
