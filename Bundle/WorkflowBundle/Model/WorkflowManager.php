<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

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
     * @var EntityBinder
     */
    protected $entityBinder;

    /**
     * @param Registry $doctrine
     * @param WorkflowRegistry $workflowRegistry
     * @param EntityBinder $entityBinder
     */
    public function __construct(Registry $doctrine, WorkflowRegistry $workflowRegistry, EntityBinder $entityBinder)
    {
        $this->doctrine = $doctrine;
        $this->workflowRegistry = $workflowRegistry;
        $this->entityBinder = $entityBinder;
    }

    /**
     * Get workflow item for given entity by name.
     *
     * @param string $workflowName
     * @param int $entityId
     * @return WorkflowItem
     */
    public function getWorkflowItem($workflowName, $entityId)
    {
        $workflow = $this->workflowRegistry->getWorkflow($workflowName);
        $entity = null;
        if ($workflow->getManagedEntityClass()) {
            $entity = $this->getWorkflowEntity($workflow, $entityId);
        }

        $managedEntityAttribute = $workflow->getManagedEntityAttribute();

        $workflowItem = $workflow->createWorkflowItem(
            array(
                $managedEntityAttribute->getName() => $entity
            )
        );

        // TODO Move binder logic to listener
        $this->entityBinder->bind($workflowItem, $entity);

        $this->doctrine->getManager()->persist($workflowItem);
        $this->doctrine->getManager()->flush();

        return $workflowItem;
    }

    /**
     * Perform workflow item transition.
     *
     * @param WorkflowItem $workflowItem
     * @param $transitionName
     * @throws \Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException
     * @throws \Oro\Bundle\WorkflowBundle\Exception\UnknownTransitionException
     * @throws \Exception
     */
    public function transit(WorkflowItem $workflowItem, $transitionName)
    {
        $workflow = $this->workflowRegistry->getWorkflow($workflowItem->getWorkflowName());

        /** @var Transition $transition */
        $transition = $workflow->getTransitions()->get($transitionName);
        if (!$transition) {
            throw new UnknownTransitionException(sprintf('Transition "%s" not found', $transitionName));
        }
        if (!$workflow->isTransitionAllowed($workflowItem, $transition)) {
            throw new ForbiddenTransitionException(
                sprintf('Transition "%s" is not allowed', $transition->getLabel())
            );
        }

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
     * Get entity that related to workflow by id
     *
     * @param Workflow $workflow
     * @param mixed $entityId
     * @throws WorkflowException
     * @throws NotManageableEntityException
     * @return mixed
     */
    protected function getWorkflowEntity(Workflow $workflow, $entityId)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManagerForClass($workflow->getManagedEntityClass());
        if (!$em) {
            throw new NotManageableEntityException($workflow->getManagedEntityClass());
        }
        $entity = $em->find($workflow->getManagedEntityClass(), $entityId);
        if (!$entity) {
            throw new WorkflowException(
                sprintf(
                    'Entity of workflow "%s" with id=%s not found',
                    $workflow->getName(),
                    $entityId
                )
            );
        }
        return $entity;
    }
}
