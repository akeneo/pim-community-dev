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
     * @var array
     */
    protected $entities;

    /**
     * @param ContainerInterface $container
     * @param array              $entities  Entities config array from search.yml
     */
    public function __construct(ContainerInterface $container, $entities)
    {
        $this->container = $container;
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
            $this->container
                 ->get('oro_search.search.engine')
                 ->save($entity, $this->container->getParameter('oro_search.realtime_update'));
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
            $this->container
                 ->get('oro_search.search.engine')
                 ->delete($entity, $this->container->getParameter('oro_search.realtime_update'));
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->postPersist($args);
    }
}
