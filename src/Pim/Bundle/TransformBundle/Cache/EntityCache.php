<?php

namespace Pim\Bundle\TransformBundle\Cache;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;

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
     * @var ReferenceRepository
     */
    protected $referenceRepository;

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
     * Sets the reference repository
     *
     * @param ReferenceRepository $referenceRepository
     */
    public function setReferenceRepository(ReferenceRepository $referenceRepository = null)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * Returns an entity by code
     *
     * @param string $class
     * @param string $code
     *
     * @return object
     */
    public function find($class, $code)
    {
        if (!isset($this->cache[$class])) {
            $this->cache[$class] = array();
        }
        if (!array_key_exists($code, $this->cache[$class])) {
            $this->cache[$class][$code] = $this->getEntity($class, $code);
        }

        return $this->cache[$class][$code];
    }

    /**
     * Sets a reference to the object
     *
     * @param object $object
     */
    public function setReference($object)
    {
        if ($this->referenceRepository && $object instanceof ReferableInterface) {
            $this->referenceRepository->setReference(
                get_class($object) . '.' . $object->getReference(),
                $object
            );
        }
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

    /**
     * Returns an entity from the manager
     *
     * @param string $class
     * @param string $code
     *
     * @return object
     */
    protected function getEntity($class, $code)
    {
        $reference = $class . '.' . $code;
        if ($this->referenceRepository && $this->referenceRepository->hasReference($reference)) {
            return $this->referenceRepository->getReference($reference);
        } else {
            return $this->doctrine
                    ->getRepository($class)
                    ->findByReference($code);
        }
    }
}
