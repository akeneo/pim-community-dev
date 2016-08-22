<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;

/**
 * Proxy for ApcuCache with a fall back on ArrayCache when using cli mode.
 *
 * In http mode, apc is enabled by default.
 *
 * In command line mode, apc is disabled by default, we advise to enable it with the option apc.enable_cli=1.
 *
 * This class replaces Doctrine\Common\Cache\ApcCache in the configuration, defined in doctrine/doctrine-bundle with
 * the parameter "doctrine.orm.cache.apc.class"
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArrayApcCache implements Cache
{
    /** @var Cache */
    protected $cache;

    /** Constructor */
    public function __construct()
    {
        $cliModeWithDisabledApc = ('cli' === php_sapi_name() && ini_get('apc.enable_cli') !== '1');
        $this->cache = $cliModeWithDisabledApc ? new ArrayCache() : new ApcuCache();
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return $this->cache->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        return $this->cache->fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->cache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->cache, $name], $arguments);
    }
}
