<?php

namespace Pim\Bundle\FlexibleEntityBundle\Manager;

use Pim\Bundle\FlexibleEntityBundle\Model\Entity;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Aims to manage simple entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleManager
{
    /**
     * @var ObjectManager $storageManager
     */
    protected $storageManager;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * Constructor
     *
     * @param string        $entityName     entity name
     * @param ObjectManager $storageManager optional storage manager, get default if not provided
     */
    public function __construct($entityName, ObjectManager $storageManager)
    {
        $this->entityName     = $entityName;
        $this->storageManager = $storageManager;
    }

    /**
     * Get object manager
     * @return ObjectManager
     */
    public function getStorageManager()
    {
        return $this->storageManager;
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityRepository()
    {
        $repo = $this->storageManager->getRepository($this->entityName);

        return $repo;
    }

    /**
     * Return a new instance
     * @return Entity
     */
    public function createEntity()
    {
        $class = $this->getEntityName();

        return new $class();
    }
}
