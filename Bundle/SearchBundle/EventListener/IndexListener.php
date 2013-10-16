<?php

namespace Oro\Bundle\SearchBundle\EventListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\SearchBundle\Engine\AbstractEngine;

class IndexListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var bool
     */
    protected $realtime;

    /**
     * @var array
     */
    protected $entities;

    /**
     * @var AbstractEngine
     */
    protected $searchEngine;

    /**
     * @var array
     */
    protected $insertEntities = array();

    /**
     * Unfortunately, can't use AbstractEngine as a parameter here due to circular reference
     *
     * @param ContainerInterface $container
     * @param bool               $realtime  Realtime update flag
     * @param array              $entities  Entities config array from search.yml
     */
    public function __construct(ContainerInterface $container, $realtime, $entities)
    {
        $this->container = $container;
        $this->realtime  = $realtime;
        $this->entities  = $entities;
    }

    /**
     * @return AbstractEngine
     */
    protected function getSearchEngine()
    {
        if (!$this->searchEngine) {
            $this->searchEngine = $this->container->get('oro_search.search.engine');
        }

        return $this->searchEngine;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (!$this->isActive()) {
            return;
        }

        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($this->isSupported($entity)) {
                $this->insertEntities[] = $entity;
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($this->isSupported($entity)) {
                $this->getSearchEngine()->save($entity, $this->realtime, true);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($this->isSupported($entity)) {
                $this->getSearchEngine()->delete($entity, true);
            }
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->isActive() || empty($this->insertEntities)) {
            return;
        }

        foreach ($this->insertEntities as $entity) {
            $this->getSearchEngine()->save($entity, $this->realtime, true);
        }
        $this->insertEntities = array();

        $args->getEntityManager()->flush();
    }

    /**
     * @return bool
     */
    protected function isActive()
    {
        return !empty($this->entities);
    }

    /**
     * @param string $entity
     * @return bool
     * TODO::don`t use get_class()
     */
    protected function isSupported($entity)
    {
        return isset($this->entities[get_class($entity)]);
    }
}
