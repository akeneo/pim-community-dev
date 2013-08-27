<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
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
     * @param Workflow $workflow
     * @param string $entityClass
     * @param mixed $entityId
     * @return array
     * @throws UnknownAttributeException
     */
    protected function getWorkflowData(Workflow $workflow, $entityClass = null, $entityId = null)
    {
        $data = array();

        // try to find appropriate entity
        if ($entityClass) {
            $managedEntityAttributes = $workflow->getManagedEntityAttributes();
            $entityAttributeName = null;

            /** @var Attribute $attribute */
            foreach ($managedEntityAttributes as $attribute) {
                if ($attribute->getOption('class') == $entityClass) {
                    $entityAttributeName = $attribute->getName();
                    break;
                }
            }

            if (!$entityAttributeName) {
                throw new UnknownAttributeException(
                    sprintf(
                        'Can\'t find attribute for managed entity %s in workflow "%s"',
                        $entityClass,
                        $workflow->getName()
                    )
                );
            }

            $data[$entityAttributeName] = $this->getWorkflowEntity($entityClass, $entityId);
        }

        return $data;
    }

    /**
     * @param string $workflowName
     * @param string|null $entityClass
     * @param mixed|null $entityId
     * @return array
     */
    public function getAllowedStartTransitions($workflowName, $entityClass = null, $entityId = null)
    {
        $workflow = $this->workflowRegistry->getWorkflow($workflowName);
        $initData = $this->getWorkflowData($workflow, $entityClass, $entityId);

        return $workflow->getAllowedStartTransitions($initData);
    }

    /**
     * @param string $workflowName
     * @param string|null $entityClass
     * @param mixed|null $entityId
     * @param string|null $transition
     * @return WorkflowItem
     */
    public function startWorkflow($workflowName, $entityClass = null, $entityId = null, $transition = null)
    {
        $workflow = $this->workflowRegistry->getWorkflow($workflowName);
        $initData = $this->getWorkflowData($workflow, $entityClass, $entityId);
        $workflowItem = $workflow->start($initData, $transition);

        $this->doctrine->getManager()->persist($workflowItem);
        $this->doctrine->getManager()->flush();

        return $workflowItem;
    }

    /**
     * Perform workflow item transition.
     *
     * @param WorkflowItem $workflowItem
     * @param string $transitionName
     * @throws \Exception
     */
    public function transit(WorkflowItem $workflowItem, $transitionName)
    {
        $workflow = $this->workflowRegistry->getWorkflow($workflowItem->getWorkflowName());
        $em = $this->doctrine->getManager();
        $em->beginTransaction();
        try {
            $workflow->transit($workflowItem, $transitionName);
            $workflowItem->setUpdated();
            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }
    }

    /**
     * Get entity that related to workflow by id
     *
     * @param string $entityClass
     * @param mixed $entityId
     * @throws WorkflowException
     * @throws NotManageableEntityException
     * @return mixed
     */
    protected function getWorkflowEntity($entityClass, $entityId)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManagerForClass($entityClass);
        if (!$em) {
            throw new NotManageableEntityException($entityClass);
        }

        $entity = $em->find($entityClass, $entityId);
        if (!$entity) {
            throw new WorkflowException(
                sprintf('Entity %s with id=%s not found', $entityClass, $entityId)
            );
        }

        return $entity;
    }

    /**
     * @param object $entity
     * @param WorkflowItem[]|Collection $workflowItems
     * @return Workflow[]
     */
    public function getApplicableWorkflows($entity, $workflowItems = null)
    {
        if (null === $workflowItems) {
            /** @var WorkflowItemRepository $workflowItemsRepository */
            $workflowItemsRepository = $this->doctrine->getRepository('OroWorkflowBundle:WorkflowItem');
            $workflowItems = $workflowItemsRepository->findWorkflowItemsByEntity($entity);
        }

        $usedWorkflows = array();
        foreach ($workflowItems as $workflowItem) {
            $usedWorkflows[] = $workflowItem->getWorkflowName();
        }

        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $allowedWorkflows = $this->workflowRegistry->getWorkflowsByEntity($entity);

        $applicableWorkflows = array();
        foreach ($allowedWorkflows as $workflow) {
            /** @var Attribute $attribute */
            foreach ($workflow->getManagedEntityAttributes() as $attribute) {
                if ($attribute->getOption('class') == $entityClass) {
                    $isMultiple = $attribute->getOption('multiple') == true;

                    // if workflow allows multiple workflow items or there is no workflow item for current class
                    if ($isMultiple || !in_array($workflow->getName(), $usedWorkflows)) {
                        $applicableWorkflows[$workflow->getName()] = $workflow;
                    }

                    break;
                }
            }
        }

        return $applicableWorkflows;
    }
}
