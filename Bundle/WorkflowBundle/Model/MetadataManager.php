<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;

class MetadataManager
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param object $entity
     * @return string
     */
    public function getEntityClass($entity)
    {
        return ClassUtils::getClass($entity);
    }

    /**
     * @param object $entity
     * @return array
     * @throws NotManageableEntityException
     */
    public function getEntityIdentifier($entity)
    {
        $entityClass = $this->getEntityClass($entity);
        if (!$this->isManageableEntity($entity)) {
            throw new NotManageableEntityException($entityClass);
        }

        if ($entity instanceof Proxy && !$entity->__isInitialized()) {
            $identifierProperty = new \ReflectionProperty(get_class($entity), '_identifier');
            $identifierProperty->setAccessible(true);
            $identifier = $identifierProperty->getValue($entity);
        } else {
            $entityManager = $this->registry->getManagerForClass($entityClass);
            $metadata = $entityManager->getClassMetadata($entityClass);
            $identifier = $metadata->getIdentifierValues($entity);
        }

        return $identifier;
    }

    /**
     * @param object $entity
     * @return bool
     */
    public function isManageableEntity($entity)
    {
        $entityClass = $this->getEntityClass($entity);
        $entityManager = $this->registry->getManagerForClass($entityClass);

        return !empty($entityManager);
    }
}
