<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Doctrine\Common\Cache\CacheProvider;
use Knp\Menu\Factory;
use Knp\Menu\ItemInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Routing\RouterInterface;

class AclAwareMenuFactoryExtension implements Factory\ExtensionInterface
{
    /**#@+
     * ACL Aware MenuFactory constants
     */
    const ACL_RESOURCE_ID_KEY = 'aclResourceId';
    const DEFAULT_ACL_POLICY = true;
    /**#@-*/

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var SecurityFacade
     */
    private $securityFacade;

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cache;

    /**
     * @var array
     */
    protected $aclCache = [];

    /**
     * @param RouterInterface $router
     * @param SecurityFacade $securityFacade
     */
    public function __construct(RouterInterface $router, SecurityFacade $securityFacade)
    {
        $this->router = $router;
        $this->securityFacade = $securityFacade;
    }

    /**
     * Set cache instance
     *
     * @param \Doctrine\Common\Cache\CacheProvider $cache
     */
    public function setCache(CacheProvider $cache)
    {
        $this->cache = $cache;
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
    public function buildOptions(array $options = [])
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
    protected function processAcl(array &$options = [])
    {
        if (isset($options['check_access']) && $options['check_access'] == false) {
            $needCheck = false;
        } else {
            $needCheck = true;
        }

        $isAllowed = self::DEFAULT_ACL_POLICY;
        if (array_key_exists(self::ACL_RESOURCE_ID_KEY, $options)) {
            if (array_key_exists($options[self::ACL_RESOURCE_ID_KEY], $this->aclCache)) {
                $isAllowed = $this->aclCache[$options[self::ACL_RESOURCE_ID_KEY]];
            } else {
                if ($needCheck) {
                    $isAllowed = $this->securityFacade->isGranted($options[self::ACL_RESOURCE_ID_KEY]);
                }

                $this->aclCache[$options[self::ACL_RESOURCE_ID_KEY]] = $isAllowed;
            }
        }

        $options['extras']['isAllowed'] = $isAllowed;
    }

    /**
     * Add uri based on route.
     *
     * @param array $options
     */
    protected function processRoute(array &$options = [])
    {
        if (!empty($options['route'])) {
            $params = [];
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
                [
                    'extras' => [
                        'routes'           => [$options['route']],
                        'routesParameters' => [$options['route']=> $params],
                    ]
                ],
                $options
            );
        }
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
