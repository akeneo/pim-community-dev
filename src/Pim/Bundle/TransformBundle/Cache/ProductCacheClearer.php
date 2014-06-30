<?php

namespace Pim\Bundle\TransformBundle\Cache;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Clears doctrine UOW and caches for the product imports
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCacheClearer
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
    protected $nonClearableEntities = array(
        'Akeneo\\Bundle\\BatchBundle\\Entity\\JobExecution',
        'Akeneo\\Bundle\\BatchBundle\\Entity\\JobInstance',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Family',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Channel',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Locale',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Currency',
        'Akeneo\\Bundle\\BatchBundle\\Entity\\StepExecution',
        'Oro\\Bundle\\UserBundle\\Entity\\User',
        'Oro\\Bundle\\OrganizationBundle\\Entity\\BusinessUnit',
        'Oro\\Bundle\\UserBundle\\Entity\\UserApi'
    );

    /**
     * Constructor
     * 
     * @param DoctrineCache     $doctrineCache
     * @param RegistryInterface $managerRegistry
     */
    function __construct(DoctrineCache $doctrineCache, RegistryInterface $managerRegistry)
    {
        $this->doctrineCache = $doctrineCache;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Clear the Unit of Work of the manager(s) from the clearable entities
     * between batch writes
     */
    public function clear()
    {
        foreach ($this->managerRegistry->getManagers() as $objectManager) {

            $identityMap = $objectManager->getUnitOfWork()->getIdentityMap();
            $managedClasses = array_keys($identityMap);
            $nonClearableClasses = array_intersect($managedClasses, $this->nonClearableEntities);

            if (empty($nonClearableClasses)) {
                $objectManager->clear();
            } else {
                $clearableClasses = array_diff($managedClasses, $this->nonClearableEntities);
                foreach ($clearableClasses as $clearableClass) {
                    $objectManager->clear($clearableClass);
                }
            }
        }
        $this->doctrineCache->clear($this->nonClearableEntities);
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
    public function setNonClearableEntities($classesList)
    {
        $this->nonClearableEntities = $classesList;
    }
}
