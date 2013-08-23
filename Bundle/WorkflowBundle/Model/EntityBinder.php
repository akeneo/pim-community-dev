<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity;

/**
 * Runs binding of workflow items with entities
 */
class EntityBinder
{
    /**
     * @var WorkflowRegistry
     */
    protected $workflowRegistry;

    /**
     * @var ManagerRegistry
     */
    protected $doctrineRegistry;

    /**
     * @param WorkflowRegistry $workflowRegistry
     * @param ManagerRegistry $doctrineRegistry
     */
    public function __construct(WorkflowRegistry $workflowRegistry, ManagerRegistry $doctrineRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * Bind entities to workflow item
     *
     * @param WorkflowItem $workflowItem
     * @return bool Returns true if new entities were binded
     */
    public function bindEntities(WorkflowItem $workflowItem)
    {
        $workflowData = $workflowItem->getData();
        if (!$workflowData->isModified()) {
            return false;
        }

        $workflow = $this->workflowRegistry->getWorkflow($workflowItem->getWorkflowName());
        $bindAttributeNames = $workflow->getBindEntityAttributeNames();
        $entitiesToBind = $workflowData->getValues($bindAttributeNames);

        $counter = 0;

        foreach ($entitiesToBind as $entity) {
            if ($entity && $this->bindEntity($workflowItem, $entity)) {
                $counter++;
            }
        }

        return $counter > 0;
    }

    /**
     * Binds entity to WorkflowItem
     *
     * @param WorkflowItem $workflowItem
     * @param mixed $entity
     * @return bool Returns true if at least one entity was bound
     */
    protected function bindEntity(WorkflowItem $workflowItem, $entity)
    {
        $bindEntity = new WorkflowBindEntity();
        $bindEntity->setEntityClass($this->getEntityClass($entity));
        $bindEntity->setEntityId($this->getEntityId($entity));

        if (!$workflowItem->hasBindEntity($bindEntity)) {
            $workflowItem->addBindEntity($bindEntity);
            return true;
        }

        return false;
    }

    /**
     * Get values of entity identifiers
     *
     * @param $entity
     * @return array
     */
    protected function getEntityId($entity)
    {
        $entityClass = get_class($entity);
        $entityManager = $this->doctrineRegistry->getManagerForClass($entityClass);
        $classMetadata = $entityManager->getClassMetadata($entityClass);
        return $classMetadata->getIdentifierValues($entity);
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
