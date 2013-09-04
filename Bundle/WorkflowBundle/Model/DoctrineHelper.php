<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;

class DoctrineHelper
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
        if ($entity instanceof Proxy && !$entity->__isInitialized()) {
            $identifierProperty = new \ReflectionProperty(get_class($entity), '_identifier');
            $identifierProperty->setAccessible(true);
            $identifier = $identifierProperty->getValue($entity);
        } else {
            $entityManager = $this->getEntityManager($entity);
            $metadata = $entityManager->getClassMetadata(get_class($entity));
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

    /**
     * @param string $entityOrClass
     * @return EntityManager
     * @throws NotManageableEntityException
     */
    protected function getEntityManager($entityOrClass)
    {
        if (is_object($entityOrClass)) {
            $entityClass = $this->getEntityClass($entityOrClass);
        } else {
            $entityClass = $entityOrClass;
        }
        $entityManager = $this->registry->getManagerForClass($entityClass);
        if (!$entityManager) {
            throw new NotManageableEntityException($entityClass);
        }

        return $entityManager;
    }

    /**
     * @param string $entityClass
     * @param mixed $entityId
     * @return mixed
     * @throws NotManageableEntityException
     */
    public function getEntityReference($entityClass, $entityId)
    {
        $entityManager = $this->getEntityManager($entityClass);
        return $entityManager->getReference($entityClass, $entityId);
    }
}
