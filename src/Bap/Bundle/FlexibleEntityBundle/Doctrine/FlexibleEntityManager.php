<?php
namespace Bap\Bundle\FlexibleEntityBundle\Doctrine;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityValue;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityField;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityGroup;

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

    /**
     * Return a new instance
     * @return Entity
     */
    public function getNewEntityInstance()
    {
        $class = $this->getEntityClass();
        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityType
     */
    public function getNewTypeInstance()
    {
        $class = $this->getTypeClass();
        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityGroup
     */
    public function getNewGroupInstance()
    {
        $class = $this->getGroupClass();
        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityField
     */
    public function getNewFieldInstance()
    {
        $class = $this->getFieldClass();
        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityValue
     */
    public function getNewValueInstance()
    {
        $class = $this->getValueClass();
        return new $class();
    }

    /**
     * Clone an entity type
     *
     * @param EntityType $entityType
     * @return EntityType
     */
    public function cloneType($entityType)
    {
        // create new entity type and clone values
        $cloneType = $this->getNewTypeInstance();
        $cloneType->setCode($entityType->getCode());
        $cloneType->setTitle($entityType->getTitle());

        // clone groups
        foreach ($entityType->getGroups() as $groupToClone) {

            // clone group entity
            $cloneGroup = $this->getNewGroupInstance();
            $cloneGroup->setTitle($groupToClone->getTitle());
            $cloneGroup->setCode($groupToClone->getCode());
            $cloneType->addGroup($cloneGroup);

            // link to same fields
            foreach ($groupToClone->getFields() as $fieldToLink) {
                $cloneGroup->addField($fieldToLink);
            }
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