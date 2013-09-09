<?php

namespace Oro\Bundle\WorkflowBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Events;

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
        return array(
            // @codingStandardsIgnoreStart
            Events::preFlush
            // @codingStandardsIgnoreEnd
        );
    }

    /**
     * Before ensure that all entities are binded to WorkflowItem
     *
     * @param PreFlushEventArgs $args
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        /** @var EntityManager $em */
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof WorkflowItem) {
                $this->binder->bindEntities($entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof WorkflowItem) {
                $this->binder->bindEntities($entity);
            }
        }
    }
}
