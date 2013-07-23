<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemEntity;
use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;

class EntityBinder
{
    /**
     * @var ManagerRegistry
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
     * @param string $assignedStep
     * @return WorkflowItemEntity
     * @throws \LogicException
     * @throws NotManageableEntityException
     */
    public function bind(WorkflowItem $item, $entity, $assignedStep = null)
    {
        if (!is_object($entity)) {
            throw new \LogicException('Bind operation requires object entity.');
        }

        $entityClass = $this->getEntityClass($entity);
        /** @var EntityManager $entityManager */
        $entityManager = $this->registry->getManagerForClass($entityClass);
        if (!$entityManager) {
            throw new NotManageableEntityException($entityClass);
        }

        $entityId = $this->getEntityId($entityManager, $entityClass, $entity);
        $itemEntityStep = $assignedStep ?: $item->getCurrentStepName();

        $itemEntity = new WorkflowItemEntity();
        $itemEntity->setEntityClass($entityClass)
            ->setEntityId($entityId)
            ->setWorkflowItem($item)
            ->setStepName($itemEntityStep);

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

        if (!$entityId) {
            throw new \LogicException('Bound object must have ID value.');
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
