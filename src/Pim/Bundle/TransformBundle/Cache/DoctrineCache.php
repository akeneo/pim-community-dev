<?php

namespace Pim\Bundle\TransformBundle\Cache;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface;

/**
 * Caches doctrine persisted objects
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DoctrineCache
{
    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var ReferenceRepository */
    protected $referenceRepository;

    /** @var array */
    protected $cache = array();

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
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
     * Returns an object by code
     *
     * @param string $class
     * @param string $code
     *
     * @return object
     */
    public function find($class, $code)
    {
        if (!isset($this->cache[$class][$code])) {
            $this->cache[$class][$code] = $this->findObject($class, $code);
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
        $this->cache = array();
    }

    /**
     * Returns an object from the manager
     *
     * @param string $class
     * @param string $code
     *
     * @return object
     */
    protected function findObject($class, $code)
    {
        $reference = $class . '.' . $code;
        if ($this->referenceRepository && $this->referenceRepository->hasReference($reference)) {
            return $this->referenceRepository->getReference($reference);
        } else {
            $repository = $this->doctrine->getManagerForClass($class)->getRepository($class);
            if (!$repository instanceof ReferableEntityRepositoryInterface) {
                throw new \Exception(
                    sprintf('Repository "%s" of class "%s" is not referable', get_class($repository), $class)
                );
            }

            return $repository->findByReference($code);
        }
    }
}
