<?php
namespace Oro\Bundle\FlexibleEntityBundle\Manager;

use Oro\Bundle\FlexibleEntityBundle\Model\Entity;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Aims to manage simple entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class SimpleEntityManager
{

    /**
     * @var ContainerInterface $container
     */
    protected $container;

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
     * @param ContainerInterface $container  service container
     * @param string             $entityName entity name
     */
    public function __construct($container, $entityName)
    {
        $this->container  = $container;
        $this->entityName = $entityName;
        $this->storageManager = $container->get('doctrine.orm.entity_manager');
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
