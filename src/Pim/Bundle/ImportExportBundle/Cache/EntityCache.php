<?php

namespace Pim\Bundle\ImportExportBundle\Cache;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Caches entities for import
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityCache
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Returns an entity by code
     *
     * @param  string $class
     * @param  string $code
     * @return object
     */
    public function find($class, $code)
    {
        if (!isset($this->cache[$class])) {
            $this->cache[$class] = array();
        }
        if (!array_key_exists($code, $this->cache[$class])) {
            $this->cache[$class][$code] = $this->doctrine
                ->getRepository($class)
                ->findOneBy(array('code'=> $code));
        }

        return $this->cache[$class][$code];
    }

    /**
     * Clears the cache
     */
    public function clear()
    {
        foreach (array_keys($this->cache) as $class) {
            $this->cache[$class] = array();
        }
    }
}
