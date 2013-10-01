<?php

namespace Oro\Bundle\WorkflowBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

/**
 * Connects WorkflowItem with WorkflowDefinition
 */
class WorkflowDefinitionSubscriber implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            // @codingStandardsIgnoreStart
            Events::prePersist
            // @codingStandardsIgnoreEnd
        );
    }

    /**
     * Before persist updates WorkflowItem with WorkflowDefinition
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof WorkflowItem) {
            $this->updateWorkflowDefinition($args->getEntityManager(), $entity);
        }
    }

    /**
     * Updates WorkflowItem with WorkflowDefinition
     *
     * @param EntityManager $em
     * @param WorkflowItem $workflowItem
     * @throws WorkflowException
     */
    protected function updateWorkflowDefinition(EntityManager $em, WorkflowItem $workflowItem)
    {
        $workflowDefinition = $em->find('OroWorkflowBundle:WorkflowDefinition', $workflowItem->getWorkflowName());
        if (!$workflowDefinition) {
            throw new WorkflowException(
                sprintf('Cannot find workflow definition "%s"', $workflowItem->getWorkflowName())
            );
        }
        $workflowItem->setDefinition($workflowDefinition);
    }
}
