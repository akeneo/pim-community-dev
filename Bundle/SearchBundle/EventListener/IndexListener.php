<?php

namespace Oro\Bundle\SearchBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\Event\LifecycleEventArgs;

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
     * Unfortunately, can't use AbstractEngine as a parameter here due to circular reference
     *
     * @param ContainerInterface $container
     * @param bool               $realtime Realtime update flag
     * @param array              $entities Entities config array from search.yml
     */
    public function __construct(ContainerInterface $container, $realtime, $entities)
    {
        $this->container = $container;
        $this->realtime  = $realtime;
        $this->entities  = $entities;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if (empty($this->entities)) {
            return;
        }

        $entity = $args->getEntity();

        // process only "indexed" entities
        if (isset($this->entities[get_class($entity)])) {
            $this->container->get('oro_search.search.engine')->save($entity, $this->realtime);
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        if (empty($this->entities)) {
            return;
        }

        $entity = $args->getEntity();

        // process only "indexed" entities
        if (isset($this->entities[get_class($entity)])) {
            $this->container->get('oro_search.search.engine')->delete($entity, $this->realtime);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->postPersist($args);
    }
}
