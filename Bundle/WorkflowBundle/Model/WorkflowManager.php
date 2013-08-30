<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository;
use Oro\Bundle\WorkflowBundle\Model\MetadataManager;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;

class WorkflowManager
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var WorkflowRegistry
     */
    protected $workflowRegistry;

    /**
     * @var MetadataManager
     */
    protected $metadataManager;

    /**
     * @param ManagerRegistry $registry
     * @param WorkflowRegistry $workflowRegistry
     * @param MetadataManager $metadataManager
     */
    public function __construct(
        ManagerRegistry $registry,
        WorkflowRegistry $workflowRegistry,
        MetadataManager $metadataManager
    ) {
        $this->registry = $registry;
        $this->workflowRegistry = $workflowRegistry;
        $this->metadataManager = $metadataManager;
    }

    /**
     * @param string|Workflow $workflow
     * @param object|null $entity
     * @return Collection
     */
    public function getAllowedStartTransitions($workflow, $entity = null)
    {
        $workflow = $this->getWorkflow($workflow);
        $initData = $this->getWorkflowData($workflow, $entity);

        return $workflow->getAllowedStartTransitions($initData);
    }

    /**
     * @param WorkflowItem $workflowItem
     * @return Collection
     */
    public function getAllowedTransitions(WorkflowItem $workflowItem)
    {
        $workflow = $this->getWorkflow($workflowItem);

        return $workflow->getAllowedTransitions($workflowItem);
    }

    /**
     * @param string $workflow
     * @param object|null $entity
     * @param string|Transition|null $transition
     * @return WorkflowItem
     * @throws \Exception
     */
    public function startWorkflow($workflow, $entity = null, $transition = null)
    {
        $workflow = $this->getWorkflow($workflow);
        $initData = $this->getWorkflowData($workflow, $entity);

        /** @var EntityManager $em */
        $em = $this->registry->getManager();
        $em->beginTransaction();
        try {
            $workflowItem = $workflow->start($initData, $transition);
            $em->persist($workflowItem);
            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        return $workflowItem;
    }

    /**
     * Perform workflow item transition.
     *
     * @param WorkflowItem $workflowItem
     * @param string|Transition $transition
     * @throws \Exception
     */
    public function transit(WorkflowItem $workflowItem, $transition)
    {
        $workflow = $this->getWorkflow($workflowItem);
        /** @var EntityManager $em */
        $em = $this->registry->getManager();
        $em->beginTransaction();
        try {
            $workflow->transit($workflowItem, $transition);
            $workflowItem->setUpdated();
            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }
    }

    /**
     * @param object $entity
     * @param WorkflowItem[]|Collection $workflowItems
     * @return Workflow[]
     */
    public function getApplicableWorkflows($entity, $workflowItems = null)
    {
        if (null === $workflowItems) {
            $workflowItems = $this->getWorkflowItemsByEntity($entity);
        }

        $usedWorkflows = array();
        foreach ($workflowItems as $workflowItem) {
            $usedWorkflows[] = $workflowItem->getWorkflowName();
        }

        $entityClass = $this->metadataManager->getEntityClass($entity);
        $allowedWorkflows = $this->workflowRegistry->getWorkflowsByEntityClass($entityClass);

        $applicableWorkflows = array();
        foreach ($allowedWorkflows as $workflow) {
            $managedEntityAttribute = $this->getManagedEntityAttributeByEntity($workflow, $entity);
            if ($managedEntityAttribute) {
                $isMultiple = $managedEntityAttribute->getOption('multiple') == true;

                // if workflow allows multiple workflow items or there is no workflow item for current class
                if ($isMultiple || !in_array($workflow->getName(), $usedWorkflows)) {
                    $applicableWorkflows[$workflow->getName()] = $workflow;
                }
            }
        }

        return $applicableWorkflows;
    }

    /**
     * Get workflow items for entity.
     *
     * @param object $entity
     * @return WorkflowItem[]
     */
    public function getWorkflowItemsByEntity($entity)
    {
        $entityClass = $this->metadataManager->getEntityClass($entity);
        $entityIdentifier = $this->metadataManager->getEntityIdentifier($entity);

        /** @var WorkflowItemRepository $workflowItemsRepository */
        $workflowItemsRepository = $this->registry->getRepository('OroWorkflowBundle:WorkflowItem');

        return $workflowItemsRepository->findByEntityMetadata($entityClass, $entityIdentifier);
    }

    /**
     * @param Workflow $workflow
     * @param object $entity
     * @return array
     * @throws UnknownAttributeException
     */
    protected function getWorkflowData(Workflow $workflow, $entity = null)
    {
        $data = array();

        // try to find appropriate entity
        if ($entity) {
            $entityAttributeName = null;
            $managedEntityAttribute = $this->getManagedEntityAttributeByEntity($workflow, $entity);
            if ($managedEntityAttribute) {
                $entityAttributeName = $managedEntityAttribute->getName();
            }

            if (!$entityAttributeName) {
                throw new UnknownAttributeException(
                    sprintf(
                        'Can\'t find attribute for managed entity in workflow "%s"',
                        $workflow->getName()
                    )
                );
            }

            $data[$entityAttributeName] = $entity;
        }

        return $data;
    }

    /**
     * @param Workflow $workflow
     * @param object $entity
     * @return null|Attribute
     */
    protected function getManagedEntityAttributeByEntity(Workflow $workflow, $entity)
    {
        $entityClass = $this->metadataManager->getEntityClass($entity);

        /** @var Attribute $attribute */
        foreach ($workflow->getManagedEntityAttributes() as $attribute) {
            if ($attribute->getOption('class') == $entityClass) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * Get workflow instance.
     *
     * string - workflow name
     * WorkflowItem - getWorkflowName() method will be used to get workflow
     * Workflow - will be returned by itself
     *
     * @param string|Workflow|WorkflowItem $workflowIdentifier
     * @throws WorkflowException
     * @return Workflow
     */
    public function getWorkflow($workflowIdentifier)
    {
        if (is_string($workflowIdentifier)) {
            return $this->workflowRegistry->getWorkflow($workflowIdentifier);
        } elseif ($workflowIdentifier instanceof WorkflowItem) {
            return $this->workflowRegistry->getWorkflow($workflowIdentifier->getWorkflowName());
        } elseif ($workflowIdentifier instanceof Workflow) {
            return $workflowIdentifier;
        }

        throw new WorkflowException('Can\'t find workflow by given identifier.');
    }
}
