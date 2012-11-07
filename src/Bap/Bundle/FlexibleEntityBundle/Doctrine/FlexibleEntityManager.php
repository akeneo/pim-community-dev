<?php
namespace Bap\Bundle\FlexibleEntityBundle\Doctrine;
use Bap\Bundle\FlexibleEntityBundle\Model\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityType;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Flexible object manager, allow to use flexible entity in storage agnostic way
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class FlexibleEntityManager
{
    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * Constructor.
     *
     * @param ObjectManager           $om
     * @param string                  $class
     */
    public function __construct(ObjectManager $om)
    {
        $this->manager = $om;
    }

    /**
     * Get object manager
     * @return ObjectManager
     */
    public function getPersistenceManager()
    {
        return $this->manager;
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public abstract function getEntityShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public abstract function getTypeShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public abstract function getGroupShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public abstract function getFieldShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public abstract function getValueShortname();

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getEntityClass()
    {
        return $this->manager->getClassMetadata($this->getEntityShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getTypeClass()
    {
        return $this->manager->getClassMetadata($this->getTypeShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getGroupClass()
    {
        return $this->manager->getClassMetadata($this->getGroupShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getFieldClass()
    {
        return $this->manager->getClassMetadata($this->getFieldShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getValueClass()
    {
        return $this->manager->getClassMetadata($this->getValueShortname())->getName();
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
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getTypeRepository()
    {
        return $this->manager->getRepository($this->getTypeShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getGroupRepository()
    {
        return $this->manager->getRepository($this->getGroupShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getFieldRepository()
    {
        return $this->manager->getRepository($this->getFieldShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getValueRepository()
    {
        return $this->manager->getRepository($this->getValueShortname());
    }

    // TODO add newEntity, newType etc


    /**
     * Clone an entity type
     *
     * @param EntityType $entityType
     * @return EntityType
     */
    public function cloneType($entityType)
    {
        // create new entity type
        $typeClass = $this->getTypeClass();
        $cloneType = new $typeClass();

        // clone entity type values
        $cloneType->setCode($entityType->getCode());
        $cloneType->setTitles($entityType->getTitles());

        // clone linked entities
        $groupClass = $this->getGroupClass();
        foreach ($entityType->getGroups() as $groupToClone) {
            // clone group entity
            $cloneGroup = new $groupClass();
            $cloneGroup->setTitle($groupToClone->getTitle());
            $cloneGroup->setCode($groupToClone->getCode());

            // clone fields
            foreach ($groupToClone->getFields() as $attributeToLink) {
                $cloneGroup->addField($attributeToLink);
            }

            // add group to default set
            $cloneType->addGroup($cloneGroup);
        }
        return $cloneType;
    }

    /**
     * Clone an entity
     *
     * @param Entity $entity
     * @return Entity
     */
    public function cloneEntity($entity)
    {
        // create a new entity
        $class = $this->getEntityClass();
        $clone = new $class();

        // clone entity type
        $cloneType = $this->cloneType($entity->getType());
        $clone->setType($cloneType);

        return $clone;
    }
}