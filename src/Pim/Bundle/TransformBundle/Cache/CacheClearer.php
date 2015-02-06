<?php

namespace Pim\Bundle\TransformBundle\Cache;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Clears doctrine UOW and caches for the product imports
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CacheClearer
{
    /**
     * @var DoctrineCache
     */
    protected $doctrineCache;

    /**
     * @var RegistryInterface
     */
    protected $managerRegistry;

    /**
     * Entities which should not be cleared on flush
     *
     * @var array
     */
    protected $nonClearableEntities = array();

    /**
     * Constructor
     *
     * @param DoctrineCache   $doctrineCache
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(DoctrineCache $doctrineCache, ManagerRegistry $managerRegistry)
    {
        $this->doctrineCache = $doctrineCache;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Clear the Unit of Work of the manager(s) from the clearable entities
     * between batch writes
     *
     * @param bool $full True to clear all entities
     */
    public function clear($full = false)
    {
        $nonClearableEntities = $full ? [] : $this->nonClearableEntities;
        foreach ($this->managerRegistry->getManagers() as $objectManager) {
            $identityMap = $objectManager->getUnitOfWork()->getIdentityMap();
            $managedClasses = array_keys($identityMap);
            $nonClearableClasses = array_intersect($managedClasses, $nonClearableEntities);

            if (empty($nonClearableClasses)) {
                $objectManager->clear();
            } else {
                $clearableClasses = array_diff($managedClasses, $nonClearableEntities);
                foreach ($clearableClasses as $clearableClass) {
                    $objectManager->clear($clearableClass);
                }
            }
        }
        $this->doctrineCache->clear($nonClearableEntities);
    }

    /**
     * Adds a non clearable entity
     *
     * @param string $class
     */
    public function addNonClearableEntity($class)
    {
        $this->nonClearableEntities[] = $class;
    }

    /**
     * Set the list of non clearable entities class.
     * Allow override of the default list.
     *
     * @param array $classesList
     */
    public function setNonClearableEntities(array $classesList)
    {
        $this->nonClearableEntities = $classesList;
    }
}
