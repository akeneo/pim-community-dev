<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemEntity;

class EntityBinder
{
    /**
     * @var EntityManager
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param WorkflowItem $item
     * @param object $entity
     * @return WorkflowItemEntity|null
     */
    public function bind(WorkflowItem $item, $entity)
    {
        if (!is_object($entity)) {
            return null;
        }

        $entityClass = $this->getEntityClass($entity);
        /** @var EntityManager $entityManager */
        $entityManager = $this->registry->getManagerForClass($entityClass);
        if (!$entityManager) {
            return null;
        }

        $entityId = $this->getEntityId($entityManager, $entityClass, $entity);

        $itemEntity = new WorkflowItemEntity();
        $itemEntity->setEntityClass($entityClass)
            ->setEntityId($entityId)
            ->setWorkflowItem($item)
            ->setStepName($item->getCurrentStepName());

        $item->addEntity($itemEntity);

        return $itemEntity;
    }

    /**
     * @param EntityManager $entityManager
     * @param $entityClass
     * @param $entity
     * @return int|null
     * @throws \LogicException
     */
    protected function getEntityId(EntityManager $entityManager, $entityClass, $entity)
    {
        $classMetadata = $entityManager->getClassMetadata($entityClass);
        $idField = $classMetadata->getSingleIdentifierFieldName();
        $entityIdValues = $classMetadata->getIdentifierValues($entity);
        $entityId = $entityIdValues[$idField];

        // if object wasn't flushed
        if (!$entityId) {
            $entityManager->persist($entity);
            $entityManager->flush($entity);

            $entityIdValues = $classMetadata->getIdentifierValues($entity);
            $entityId = $entityIdValues[$idField];
        }

        if (!$entityId) {
            throw new \LogicException(sprintf('Can\'t extract entity ID from class %s', $entityClass));
        }

        return $entityId;
    }

    /**
     * @param object $entity
     * @return string
     */
    protected function getEntityClass($entity)
    {
        return ClassUtils::getRealClass(get_class($entity));
    }
}
