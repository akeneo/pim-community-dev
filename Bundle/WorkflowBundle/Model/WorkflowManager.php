<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository;

class WorkflowManager
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var WorkflowRegistry
     */
    protected $workflowRegistry;

    /**
     * @param Registry $doctrine
     * @param WorkflowRegistry $workflowRegistry
     */
    public function __construct(Registry $doctrine, WorkflowRegistry $workflowRegistry)
    {
        $this->doctrine = $doctrine;
        $this->workflowRegistry = $workflowRegistry;
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
     */
    public function startWorkflow($workflow, $entity = null, $transition = null)
    {
        $workflow = $this->getWorkflow($workflow);
        $initData = $this->getWorkflowData($workflow, $entity);
        $workflowItem = $workflow->start($initData, $transition);

        $this->doctrine->getManager()->persist($workflowItem);
        $this->doctrine->getManager()->flush();

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
        $em = $this->doctrine->getManager();
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

        $allowedWorkflows = $this->workflowRegistry->getWorkflowsByEntity($entity);

        $applicableWorkflows = array();
        foreach ($allowedWorkflows as $workflow) {
            /** @var Attribute $attribute */
            $managedEntityAttribute = $this->getManagedEntityAttributesByEntity($workflow, $entity);
            $isMultiple = $managedEntityAttribute->getOption('multiple') == true;

            // if workflow allows multiple workflow items or there is no workflow item for current class
            if ($isMultiple || !in_array($workflow->getName(), $usedWorkflows)) {
                $applicableWorkflows[$workflow->getName()] = $workflow;
            }
        }

        return $applicableWorkflows;
    }

    /**
     * Get workflow items for entity.
     *
     * @param object $entity
     * @return array
     */
    public function getWorkflowItemsByEntity($entity)
    {
        /** @var WorkflowItemRepository $workflowItemsRepository */
        $workflowItemsRepository = $this->doctrine->getRepository('OroWorkflowBundle:WorkflowItem');

        return $workflowItemsRepository->findWorkflowItemsByEntity($entity);
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
            $managedEntityAttribute = $this->getManagedEntityAttributesByEntity($workflow, $entity);
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
    protected function getManagedEntityAttributesByEntity(Workflow $workflow, $entity)
    {
        /** @var Attribute $attribute */
        $entityClass = ClassUtils::getRealClass(get_class($entity));
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
        throw new WorkflowException('Could not find workflow by given identifier.');
    }
}
