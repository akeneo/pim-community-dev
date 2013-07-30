<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Doctrine\Common\Cache\CacheProvider;
use Knp\Menu\Factory;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Oro\Bundle\UserBundle\Acl\Manager;

class AclAwareMenuFactoryExtension implements Factory\ExtensionInterface
{
    /**#@+
     * ACL Aware MenuFactory constants
     */
    const ACL_RESOURCE_ID_KEY = 'aclResourceId';
    const ROUTE_CONTROLLER_KEY = '_controller';
    const CONTROLLER_ACTION_DELIMITER = '::';
    const DEFAULT_ACL_POLICY = true;
    const CACHE_NAMESPACE = 'oro_menu.cache';
    /**#@-*/

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Oro\Bundle\UserBundle\Acl\Manager
     */
    private $aclManager;

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cache;

    /**
     * @var array
     */
    protected $aclCache = array();

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Oro\Bundle\UserBundle\Acl\Manager         $aclManager
     */
    public function __construct(RouterInterface $router, Manager $aclManager)
    {
        $this->router = $router;
        $this->aclManager = $aclManager;
    }

    /**
     * Set cache instance
     *
     * @param \Doctrine\Common\Cache\CacheProvider $cache
     */
    public function setCache(CacheProvider $cache)
    {
        $this->cache = $cache;
        $this->cache->setNamespace(self::CACHE_NAMESPACE);
    }

    /**
     * Configures the item with the passed options
     *
     * @param ItemInterface $item
     * @param array         $options
     */
    public function buildItem(ItemInterface $item, array $options)
    {

    }

    /**
     * Check Permissions and set options for renderer.
     *
     * @param  array $options
     * @return array
     */
    public function buildOptions(array $options = array())
    {
        $this->processAcl($options);

        $hasNonAuth = array_key_exists('showNonAuthorized', $options['extras']);
        if ($options['extras']['isAllowed']
            || ($hasNonAuth && $options['extras']['showNonAuthorized'])
        ) {
            $this->processRoute($options);
        }

        return $options;
    }

    /**
     * Check ACL based on acl_resource_id, route or uri.
     *
     * @param array $options
     */
    protected function processAcl(array &$options = array())
    {
        $isAllowed = self::DEFAULT_ACL_POLICY;
        if (array_key_exists(self::ACL_RESOURCE_ID_KEY, $options)) {
            if (array_key_exists($options[self::ACL_RESOURCE_ID_KEY], $this->aclCache)) {
                $isAllowed = $this->aclCache[$options[self::ACL_RESOURCE_ID_KEY]];
            } else {
                $isAllowed = $this->aclManager->isResourceGranted($options[self::ACL_RESOURCE_ID_KEY]);
                $this->aclCache[$options[self::ACL_RESOURCE_ID_KEY]] = $isAllowed;
            }
        } else {
            $routeInfo = $this->getRouteInfo($options);
            if ($routeInfo) {
                if (array_key_exists($routeInfo['key'], $this->aclCache)) {
                    $isAllowed = $this->aclCache[$routeInfo['key']];
                } else {
                    $isAllowed = $this->aclManager
                        ->isClassMethodGranted($routeInfo['controller'], $routeInfo['action']);
                    $this->aclCache[$routeInfo['key']] = $isAllowed;
                }
            }
        }

        $options['extras']['isAllowed'] = $isAllowed;
    }

    /**
     * Add uri based on route.
     *
     * @param array $options
     */
    protected function processRoute(array &$options = array())
    {
        if (!empty($options['route'])) {
            $params = array();
            if (isset($options['routeParameters'])) {
                $params = $options['routeParameters'];
            }
            $cacheKey = null;
            $hasInCache = false;
            $uri = null;
            if ($this->cache) {
                $cacheKey = $this->getCacheKey('route_uri', $options['route'] . ($params ? serialize($params) : ''));
                if ($this->cache->contains($cacheKey)) {
                    $uri = $this->cache->fetch($cacheKey);
                    $hasInCache = true;
                }
            }
            if (!$hasInCache) {
                $absolute = false;
                if (isset($options['routeAbsolute'])) {
                    $absolute = $options['routeAbsolute'];
                }
                $uri = $this->router->generate($options['route'], $params, $absolute);
                if ($this->cache) {
                    $this->cache->save($cacheKey, $uri);
                }
            }

            $options['uri'] = $uri;

            $options = array_merge_recursive(
                array(
                    'extras' => array(
                        'routes' => array($options['route']),
                        'routesParameters' => array($options['route']=>$params),
                    )
                ),
                $options
            );
        }
    }

    /**
     * Get route information based on MenuItem options
     *
     * @param  array         $options
     * @return array|boolean
     */
    protected function getRouteInfo(array $options = array())
    {
        $key = null;
        $cacheKey = null;
        $hasInCache = false;
        if (array_key_exists('route', $options)) {
            if ($this->cache) {
                $cacheKey = $this->getCacheKey('route_acl', $options['route']);
                if ($this->cache->contains($cacheKey)) {
                    $key = $this->cache->fetch($cacheKey);
                    $hasInCache = true;
                }
            }
            if (!$hasInCache) {
                $key = $this->getRouteInfoByRouteName($options['route']);
            }
        } elseif (array_key_exists('uri', $options)) {
            if ($this->cache) {
                $cacheKey = $this->getCacheKey('uri_acl', $options['uri']);
                if ($this->cache->contains($cacheKey)) {
                    $key = $this->cache->fetch($cacheKey);
                    $hasInCache = true;
                }
            }
            if (!$hasInCache) {
                $key = $this->getRouteInfoByUri($options['uri']);
            }
        }

        if ($this->cache && !$hasInCache) {
            $this->cache->save($cacheKey, $key);
        }

        $info = explode(self::CONTROLLER_ACTION_DELIMITER, $key);
        if (count($info) == 2) {
            return array(
                'controller' => $info[0],
                'action' => $info[1],
                'key' => $key
            );
        } else {
            return false;
        }
    }

    /**
     * Get route info by route name
     *
     * @param $routeName
     * @return string|null
     */
    protected function getRouteInfoByRouteName($routeName)
    {
        $route = $this->router->getRouteCollection()->get($routeName);
        if ($route) {
            return $route->getDefault(self::ROUTE_CONTROLLER_KEY);
        }

        return null;
    }

    /**
     * Get route info by uri
     *
     * @param  string      $uri
     * @return null|string
     */
    protected function getRouteInfoByUri($uri)
    {
        try {
            $routeInfo = $this->router->match($uri);

            return $routeInfo[self::ROUTE_CONTROLLER_KEY];
        } catch (ResourceNotFoundException $e) {
        }

        return null;
    }

    /**
     * Get safe cache key
     *
     * @param  string $space
     * @param  string $value
     * @return string
     */
    protected function getCacheKey($space, $value)
    {
        return md5($space . ':' . $value);
    }
}
