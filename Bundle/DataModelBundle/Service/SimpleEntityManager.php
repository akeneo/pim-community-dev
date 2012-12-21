<?php
namespace Oro\Bundle\DataModelBundle\Service;

use Oro\Bundle\DataModelBundle\Model\Entity;

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
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $entityShortname;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     * @param string        $entitySN
     */
    public function __construct(ObjectManager $om, $entitySN)
    {
        $this->manager = $om;
        $this->entityShortname = $entitySN;
    }

    /**
     * Get object manager
     * @return ObjectManager
     */
    public function getStorageManager()
    {
        return $this->manager;
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getEntityShortname()
    {
        return $this->entityShortname;
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getEntityClass()
    {
        return $this->manager->getClassMetadata($this->getEntityShortname())->getName();
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityRepository()
    {
        return $this->manager->getRepository($this->getEntityShortname());
    }

    /**
     * Return a new instance
     * @return Entity
     */
    public function getNewEntityInstance()
    {
        $class = $this->getEntityClass();

        return new $class();
    }

}
