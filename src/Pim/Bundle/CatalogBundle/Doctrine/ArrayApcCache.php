<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;

/**
 * Proxy for ApcCache in http mode and ArrayCache in command line mode
 *
 * This class replaces Doctrine\Common\Cache\ApcCache in the configuration
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArrayApcCache implements Cache
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cache = ('cli' === php_sapi_name())
            ? new ArrayCache()
            : new ApcCache();
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
        return call_user_func_array(array($this->cache, $name), $arguments);
    }
}
