<?php

namespace Oro\Bundle\WorkflowBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\EntityBinder;

/**
 * Runs binding of workflow items with entities
 */
class WorkflowBindEntitySubscriber implements EventSubscriber
{
    /**
     * @var EntityBinder
     */
    protected $binder;

    /**
     * Constructor
     *
     * @param EntityBinder $binder
     */
    public function __construct(EntityBinder $binder)
    {
        $this->binder = $binder;
    }


    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array('onFlush');
    }

    /**
     * Before ensure that all entities are binded to WorkflowItem
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        /** @var EntityManager $em */
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($this->isSupported($entity)) {
                $this->bindEntities($entity, $uow);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($this->isSupported($entity)) {
                $this->bindEntities($entity, $uow);
            }
        }
    }

    /**
     * Bind entities of WorkflowItem and triggers uow
     *
     * @param WorkflowItem $workflowItem
     * @param UnitOfWork $uow
     */
    protected function bindEntities(WorkflowItem $workflowItem, UnitOfWork $uow)
    {
        if ($this->binder->bindEntities($workflowItem)) {
            $uow->propertyChanged($workflowItem, 'bindEntities', null, $workflowItem->getBindEntities());
        }
    }

    /**
     * @param $entity
     * @return bool
     */
    protected function isSupported($entity)
    {
        return $entity instanceof WorkflowItem;
    }
}
